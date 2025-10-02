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
            $accessToken = $this->getAccessToken();
            
            $response = Http::withToken($accessToken)
                ->get($this->graphApiBaseUrl . '/users/' . urlencode($upn));

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

            // User not found
            if ($response->status() === 404) {
                Log::channel('azure')->info('User not found in Azure AD', ['upn' => $upn]);
                return null;
            }

            // Other error
            throw new Exception("Failed to find user: " . $response->body());

        } catch (Exception $e) {
            // If Azure returns "not found", handle gracefully
            if (str_contains($e->getMessage(), 'Request_ResourceNotFound') || 
                str_contains($e->getMessage(), '404')) {
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
     * Create a new user in Azure AD
     * 
     * @param array $userData
     * @return array
     * @throws Exception
     */
    public function createUser(array $userData): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            // Generate UPN based on strategy
            $upn = $this->generateUPN($userData);
            
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
            
            $response = Http::withToken($accessToken)
                ->post($this->graphApiBaseUrl . '/users', $requestBody);
            
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
            $accessToken = $this->getAccessToken();
            
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
            
            $response = Http::withToken($accessToken)
                ->patch($this->graphApiBaseUrl . "/users/{$azureId}", $requestBody);
            
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
            $accessToken = $this->getAccessToken();
            
            $response = Http::withToken($accessToken)
                ->patch($this->graphApiBaseUrl . "/users/{$azureId}", [
                    'accountEnabled' => false
                ]);
            
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
            $accessToken = $this->getAccessToken();
            
            $response = Http::withToken($accessToken)
                ->patch($this->graphApiBaseUrl . "/users/{$azureId}", [
                    'accountEnabled' => true
                ]);
            
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
            $accessToken = $this->getAccessToken();
            
            $response = Http::withToken($accessToken)
                ->delete($this->graphApiBaseUrl . "/users/{$azureId}");
            
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
            $accessToken = $this->getAccessToken();
            
            $response = Http::withToken($accessToken)
                ->post($this->graphApiBaseUrl . "/groups/{$groupId}/members/\$ref", [
                    '@odata.id' => $this->graphApiBaseUrl . "/directoryObjects/{$azureUserId}"
                ]);
            
            // 204 = success, 400 with "already exists" = also OK
            if ($response->successful() || $response->status() === 204) {
                Log::channel('azure')->info('User added to group', [
                    'azure_user_id' => $azureUserId,
                    'group_id' => $groupId
                ]);
                return true;
            }
            
            // Check if user is already a member
            if ($response->status() === 400 && 
                str_contains($response->body(), 'already exist')) {
                Log::channel('azure')->info('User already in group', [
                    'azure_user_id' => $azureUserId,
                    'group_id' => $groupId
                ]);
                return true;
            }
            
            throw new Exception(
                'Failed to add user to group: ' . $response->body()
            );
            
        } catch (Exception $e) {
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
            $accessToken = $this->getAccessToken();
            
            $response = Http::withToken($accessToken)
                ->delete($this->graphApiBaseUrl . "/groups/{$groupId}/members/{$azureUserId}/\$ref");
            
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
            Log::channel('azure')->error('Failed to remove user from group', [
                'azure_user_id' => $azureUserId,
                'group_id' => $groupId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}