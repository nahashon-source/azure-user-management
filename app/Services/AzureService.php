<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class AzureService
{
    // Azure Configuration Properties
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $graphApiBaseUrl;
    private string $authorityBaseUrl;
    private string $defaultDomain;
    
    // Token Management
    private string $tokenCacheKey;
    private int $tokenCacheDuration;

    /**
     * Initialize Azure Service with configuration
     */
    public function __construct()
    {
        $this->tenantId = config('azure.tenant_id');
        $this->clientId = config('azure.client_id');
        $this->clientSecret = config('azure.client_secret');
        $this->graphApiBaseUrl = config('azure.graph_api_base_url');
        $this->authorityBaseUrl = config('azure.authority_base_url');
        $this->defaultDomain = config('azure.default_domain');
        $this->tokenCacheKey = config('azure.token_cache_key');
        $this->tokenCacheDuration = config('azure.token_cache_duration');

        // Validate required configuration
        $this->validateConfiguration();
    }

    /**
     * Validate that all required Azure configuration is present
     * 
     * @throws Exception
     */
    private function validateConfiguration(): void
    {
        $required = [
            'tenant_id' => $this->tenantId,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ];

        $missing = [];
        foreach ($required as $key => $value) {
            if (empty($value)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new Exception(
                'Missing required Azure configuration: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Make HTTP request with retry logic and exponential backoff
     * 
     * @param string $method HTTP method (GET, POST, PATCH, DELETE)
     * @param string $url Full URL to call
     * @param array|null $data Request body data
     * @param int $maxRetries Maximum number of retry attempts
     * @return \Illuminate\Http\Client\Response
     * @throws Exception
     */
    private function makeRequestWithRetry(string $method, string $url, ?array $data = null, int $maxRetries = 3)
    {
        $attempt = 0;
        $lastException = null;
        
        while ($attempt < $maxRetries) {
            try {
                $token = $this->getAccessToken();
                $request = Http::withToken($token)->timeout(30);
                
                $response = match(strtoupper($method)) {
                    'GET' => $request->get($url),
                    'POST' => $request->post($url, $data ?? []),
                    'PATCH' => $request->patch($url, $data ?? []),
                    'DELETE' => $request->delete($url),
                    default => throw new Exception("Unsupported HTTP method: {$method}")
                };
                
                // Success - return response
                if ($response->successful()) {
                    if ($attempt > 0) {
                        Log::channel('azure')->info('Request succeeded after retry', [
                            'method' => $method,
                            'url' => $url,
                            'attempts' => $attempt + 1
                        ]);
                    }
                    return $response;
                }
                
                // Rate limited (429) - wait and retry
                if ($response->status() === 429) {
                    $retryAfter = $response->header('Retry-After') ?? (2 ** $attempt);
                    
                    Log::channel('azure')->warning('Rate limited, retrying', [
                        'method' => $method,
                        'url' => $url,
                        'attempt' => $attempt + 1,
                        'retry_after' => $retryAfter
                    ]);
                    
                    sleep((int)$retryAfter);
                    $attempt++;
                    continue;
                }
                
                // Server errors (5xx) - retry with backoff
                if ($response->status() >= 500) {
                    $attempt++;
                    
                    if ($attempt < $maxRetries) {
                        $backoffSeconds = 2 ** ($attempt - 1);
                        
                        Log::channel('azure')->warning('Server error, retrying', [
                            'method' => $method,
                            'url' => $url,
                            'status' => $response->status(),
                            'attempt' => $attempt,
                            'backoff_seconds' => $backoffSeconds
                        ]);
                        
                        sleep($backoffSeconds);
                        continue;
                    }
                }
                
                // Client errors (4xx) or max retries reached - throw exception
                throw new Exception("HTTP {$response->status()}: {$response->body()}");
                
            } catch (Exception $e) {
                $lastException = $e;
                $attempt++;
                
                // Don't retry for client errors (except 429 which is handled above)
                if (str_contains($e->getMessage(), 'HTTP 4')) {
                    throw $e;
                }
                
                if ($attempt < $maxRetries) {
                    $backoffSeconds = 2 ** ($attempt - 1);
                    
                    Log::channel('azure')->warning('Request failed, retrying', [
                        'method' => $method,
                        'url' => $url,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                        'backoff_seconds' => $backoffSeconds
                    ]);
                    
                    sleep($backoffSeconds);
                } else {
                    Log::channel('azure')->error('Request failed after all retries', [
                        'method' => $method,
                        'url' => $url,
                        'attempts' => $maxRetries,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        throw $lastException ?? new Exception('Request failed after all retries');
    }

    /**
     * Get access token from Azure AD (with caching)
     * 
     * @return string
     * @throws Exception
     */
    public function getAccessToken(): string
    {
        // Try to get cached token first
        $cachedToken = Cache::get($this->tokenCacheKey);
        
        if ($cachedToken) {
            Log::channel('azure')->debug('Using cached access token');
            return $cachedToken;
        }

        // Request new token
        try {
            $tokenUrl = "{$this->authorityBaseUrl}/{$this->tenantId}/oauth2/v2.0/token";
            
            $response = Http::asForm()->post($tokenUrl, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ]);

            if (!$response->successful()) {
                throw new Exception(
                    'Failed to obtain access token: ' . $response->body()
                );
            }

            $data = $response->json();
            $accessToken = $data['access_token'];

            // Cache the token (expires in 55 minutes, actual expiry is 60)
            Cache::put($this->tokenCacheKey, $accessToken, now()->addMinutes($this->tokenCacheDuration));

            Log::channel('azure')->info('New access token obtained and cached');

            return $accessToken;

        } catch (Exception $e) {
            Log::channel('azure')->error('Failed to get access token', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Find a user in Azure AD by User Principal Name (UPN / email).
     *
     * @param string $upn
     * @return array|null
     * @throws Exception
     */
    public function findUserByUPN(string $upn): ?array
    {
        try {
            $response = $this->makeRequestWithRetry(
                'GET',
                $this->graphApiBaseUrl . '/users/' . urlencode($upn)
            );

            if ($response->successful()) {
                $userData = $response->json();
                
                Log::channel('azure')->info('User found in Azure AD', [
                    'upn' => $upn,
                    'azure_id' => $userData['id']
                ]);
                
                return [
                    'id' => $userData['id'],
                    'userPrincipalName' => $userData['userPrincipalName'],
                    'displayName' => $userData['displayName'],
                ];
            }

            return null;

        } catch (Exception $e) {
            // If Azure returns "not found", handle gracefully
            if (str_contains($e->getMessage(), 'Request_ResourceNotFound') || 
                str_contains($e->getMessage(), 'HTTP 404')) {
                Log::channel('azure')->info('User not found in Azure AD', ['upn' => $upn]);
                return null;
            }

            Log::channel('azure')->error('Failed to find user by UPN', [
                'upn' => $upn,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

   /**
 * Create a new user in Azure AD (with duplicate check)
 * 
 * @param array $userData
 * @return array
 * @throws Exception
 */
public function createUser(array $userData): array
{
    try {
        // Generate UPN based on strategy
        $upn = $this->generateUPN($userData);

        Log::channel('azure')->debug('Generated UPN for user creation', [
            'generated_upn' => $upn,
            'user_name' => $userData['name'],
            'user_data' => $userData
        ]);
        
        // ✅ CHECK IF USER ALREADY EXISTS IN AZURE
        Log::channel('azure')->info('Checking if user exists in Azure AD', [
            'upn' => $upn
        ]);
        
        $existingUser = $this->findUserByUPN($upn);
        
        if ($existingUser) {
            Log::channel('azure')->warning('User already exists in Azure AD, returning existing user', [
                'upn' => $upn,
                'azure_id' => $existingUser['id']
            ]);
            
            // Return existing user data instead of failing
            return [
                'azure_id' => $existingUser['id'],
                'azure_upn' => $existingUser['userPrincipalName'],
                'azure_display_name' => $existingUser['displayName'],
                'temporary_password' => null, // No new password since user exists
            ];
        }
        
        // User doesn't exist, proceed with creation
        Log::channel('azure')->info('User not found in Azure, creating new user', [
            'upn' => $upn
        ]);
        
        // Generate temporary password
        $temporaryPassword = $this->generateSecurePassword();
        
        $requestBody = [
            'accountEnabled' => true,
            'displayName' => $userData['name'],
            'mailNickname' => $this->generateMailNickname($userData),
            'userPrincipalName' => $upn,
            'passwordProfile' => [
                'forceChangePasswordNextSignIn' => config('azure.force_password_change', true),
                'password' => $temporaryPassword,
            ],
        ];
        
        // Optional fields
        if (!empty($userData['email'])) {
            $requestBody['mail'] = $userData['email'];
        }
        if (!empty($userData['phone'])) {
            $requestBody['mobilePhone'] = $userData['phone'];
        }
        if (!empty($userData['job_title'])) {
            $requestBody['jobTitle'] = $userData['job_title'];
        }
        if (!empty($userData['department'])) {
            $requestBody['department'] = $userData['department'];
        }
        if (!empty($userData['employee_id'])) {
            $requestBody['employeeId'] = $userData['employee_id'];
        }
        
        $response = $this->makeRequestWithRetry(
            'POST',
            $this->graphApiBaseUrl . '/users',
            $requestBody
        );
        
        if (!$response->successful()) {
            throw new Exception(
                'Failed to create user in Azure AD: ' . $response->body()
            );
        }
        
        $azureUser = $response->json();
        
        Log::channel('azure')->info('User created in Azure AD', [
            'azure_id' => $azureUser['id'],
            'upn' => $azureUser['userPrincipalName']
        ]);
        
        return [
            'azure_id' => $azureUser['id'],
            'azure_upn' => $azureUser['userPrincipalName'],
            'azure_display_name' => $azureUser['displayName'],
            'temporary_password' => $temporaryPassword,
        ];
        
    } catch (Exception $e) {
        Log::channel('azure')->error('Failed to create user', [
            'userData' => $userData,
            'error' => $e->getMessage()
        ]);
        throw $e;
    }
}

    /**
     * Update an existing user in Azure AD
     * 
     * @param string $azureId
     * @param array $updateData
     * @return bool
     * @throws Exception
     */
    public function updateUser(string $azureId, array $updateData): bool
    {
        try {
            $requestBody = [];
            
            if (isset($updateData['name'])) {
                $requestBody['displayName'] = $updateData['name'];
            }
            if (isset($updateData['phone'])) {
                $requestBody['mobilePhone'] = $updateData['phone'];
            }
            if (isset($updateData['job_title'])) {
                $requestBody['jobTitle'] = $updateData['job_title'];
            }
            if (isset($updateData['department'])) {
                $requestBody['department'] = $updateData['department'];
            }
            
            if (empty($requestBody)) {
                return true; // Nothing to update
            }
            
            $response = $this->makeRequestWithRetry(
                'PATCH',
                $this->graphApiBaseUrl . "/users/{$azureId}",
                $requestBody
            );
            
            if (!$response->successful()) {
                throw new Exception(
                    'Failed to update user in Azure AD: ' . $response->body()
                );
            }
            
            Log::channel('azure')->info('User updated in Azure AD', [
                'azure_id' => $azureId
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::channel('azure')->error('Failed to update user', [
                'azure_id' => $azureId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    

    /**
     * Disable a user in Azure AD
     * 
     * @param string $azureId
     * @return bool
     * @throws Exception
     */
    public function disableUser(string $azureId): bool
    {
        try {
            $response = $this->makeRequestWithRetry(
                'PATCH',
                $this->graphApiBaseUrl . "/users/{$azureId}",
                ['accountEnabled' => false]
            );
            
            if (!$response->successful()) {
                throw new Exception(
                    'Failed to disable user in Azure AD: ' . $response->body()
                );
            }
            
            Log::channel('azure')->info('User disabled in Azure AD', [
                'azure_id' => $azureId
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::channel('azure')->error('Failed to disable user', [
                'azure_id' => $azureId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enable a user in Azure AD
     * 
     * @param string $azureId
     * @return bool
     * @throws Exception
     */
    public function enableUser(string $azureId): bool
    {
        try {
            $response = $this->makeRequestWithRetry(
                'PATCH',
                $this->graphApiBaseUrl . "/users/{$azureId}",
                ['accountEnabled' => true]
            );
            
            if (!$response->successful()) {
                throw new Exception(
                    'Failed to enable user in Azure AD: ' . $response->body()
                );
            }
            
            Log::channel('azure')->info('User enabled in Azure AD', [
                'azure_id' => $azureId
            ]);
            
            return true;
            
        } catch (Exception $e) {
            Log::channel('azure')->error('Failed to enable user', [
                'azure_id' => $azureId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a user from Azure AD
     * 
     * @param string $azureId
     * @return bool
     * @throws Exception
     */
    public function deleteUser(string $azureId): bool
    {
        try {
            $response = $this->makeRequestWithRetry(
                'DELETE',
                $this->graphApiBaseUrl . "/users/{$azureId}"
            );
            
            if (!$response->successful() && $response->status() !== 404) {
                throw new Exception(
                    'Failed to delete user from Azure AD: ' . $response->body()
                );
            }
            
            Log::channel('azure')->info('User deleted from Azure AD', [
                'azure_id' => $azureId
            ]);
            
            return true;
            
        } catch (Exception $e) {
            // 404 is acceptable - user already deleted
            if (str_contains($e->getMessage(), 'HTTP 404')) {
                return true;
            }
            
            Log::channel('azure')->error('Failed to delete user', [
                'azure_id' => $azureId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate User Principal Name (UPN)
     * 
     * @param array $userData
     * @return string
     */
    private function generateUPN(array $userData): string
    {
        $strategy = config('azure.upn_strategy', 'name_based');
        
        if ($strategy === 'name_based') {
            // firstname.lastname@domain.com
            $nameParts = explode(' ', strtolower($userData['name']));
            $firstName = $nameParts[0] ?? 'user';
            $lastName = end($nameParts);
            
            return "{$firstName}.{$lastName}@{$this->defaultDomain}";
        }
        
        // employee_id based: employeeID@domain.com
        return strtolower($userData['employee_id']) . "@{$this->defaultDomain}";
    }

    /**
     * Generate mail nickname from name
     * 
     * @param array $userData
     * @return string
     */
    private function generateMailNickname(array $userData): string
    {
        $nameParts = explode(' ', strtolower($userData['name']));
        $firstName = $nameParts[0] ?? 'user';
        $lastName = end($nameParts);
        
        return "{$firstName}.{$lastName}";
    }

    /**
     * Generate a secure random password
     * 
     * @return string
     */
    private function generateSecurePassword(): string
    {
        $length = config('azure.default_password_length', 16);
        
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    /**
     * Add user to Azure AD Group
     * 
     * @param string $azureUserId
     * @param string $groupId
     * @return bool
     * @throws Exception
     */
    public function addUserToGroup(string $azureUserId, string $groupId): bool
    {
        try {
            $response = $this->makeRequestWithRetry(
                'POST',
                $this->graphApiBaseUrl . "/groups/{$groupId}/members/\$ref",
                ['@odata.id' => $this->graphApiBaseUrl . "/directoryObjects/{$azureUserId}"]
            );
            
            // 204 = success, 400 with "already exists" = also OK
            if ($response->successful() || $response->status() === 204) {
                Log::channel('azure')->info('User added to group', [
                    'azure_user_id' => $azureUserId,
                    'group_id' => $groupId
                ]);
                return true;
            }
            
            throw new Exception(
                'Failed to add user to group: ' . $response->body()
            );
            
        } catch (Exception $e) {
            // Check if user is already a member
            if (str_contains($e->getMessage(), 'already exist')) {
                Log::channel('azure')->info('User already in group', [
                    'azure_user_id' => $azureUserId,
                    'group_id' => $groupId
                ]);
                return true;
            }
            
            Log::channel('azure')->error('Failed to add user to group', [
                'azure_user_id' => $azureUserId,
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Remove user from Azure AD Group
     * 
     * @param string $azureUserId
     * @param string $groupId
     * @return bool
     * @throws Exception
     */
    public function removeUserFromGroup(string $azureUserId, string $groupId): bool
    {
        try {
            $response = $this->makeRequestWithRetry(
                'DELETE',
                $this->graphApiBaseUrl . "/groups/{$groupId}/members/{$azureUserId}/\$ref"
            );
            
            if ($response->successful() || $response->status() === 204 || $response->status() === 404) {
                Log::channel('azure')->info('User removed from group', [
                    'azure_user_id' => $azureUserId,
                    'group_id' => $groupId
                ]);
                return true;
            }
            
            throw new Exception(
                'Failed to remove user from group: ' . $response->body()
            );
            
        } catch (Exception $e) {
            // 404 is acceptable - user already removed
            if (str_contains($e->getMessage(), 'HTTP 404')) {
                return true;
            }
            
            Log::channel('azure')->error('Failed to remove user from group', [
                'azure_user_id' => $azureUserId,
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}