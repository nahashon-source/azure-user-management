<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class AzureService
{
    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $graphBaseUrl;
    private string $authorityUrl;
    
    public function __construct()
    {
        $this->tenantId = config('azure.tenant_id');
        $this->clientId = config('azure.client_id');
        $this->clientSecret = config('azure.client_secret');
        $this->graphBaseUrl = config('azure.graph_api_base_url');
        $this->authorityUrl = config('azure.authority_base_url');
    }

    /**
     * Get Azure AD access token (with caching)
     */
    public function getAccessToken(): string
    {
        $cacheKey = 'azure_access_token';
        
        // Check if we have a cached token
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::asForm()->post("{$this->authorityUrl}/{$this->tenantId}/oauth2/v2.0/token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ]);

            if ($response->failed()) {
                Log::error('Azure token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Failed to obtain Azure access token: ' . $response->body());
            }

            $data = $response->json();
            $token = $data['access_token'];
            
            // Cache token for 55 minutes (tokens typically expire in 60 minutes)
            Cache::put($cacheKey, $token, now()->addMinutes(55));
            
            return $token;

        } catch (Exception $e) {
            Log::error('Azure authentication error', ['error' => $e->getMessage()]);
            throw new Exception('Azure authentication failed: ' . $e->getMessage());
        }
    }

    /**
     * Create user in Azure AD
     * 
     * @param array $userData
     * @return array Azure user data
     */
    public function createUser(array $userData): array
    {
        try {
            $token = $this->getAccessToken();
            
            // Generate both email formats
            $primaryEmail = $this->generateEmail($userData['name'], $userData['employee_id']);
            $mailNickname = $this->generateMailNickname($userData['name']);
            
            $azureUserData = [
                'accountEnabled' => true,
                'displayName' => $userData['name'],
                'mailNickname' => $mailNickname,
                'userPrincipalName' => $primaryEmail,
                'passwordProfile' => [
                    'forceChangePasswordNextSignIn' => true,
                    'password' => $this->generateSecurePassword()
                ],
                'givenName' => $this->getFirstName($userData['name']),
                'surname' => $this->getLastName($userData['name']),
                'jobTitle' => $userData['job_title'] ?? 'Employee',
                'department' => $userData['department'] ?? null,
                'officeLocation' => $userData['location'] ?? null,
                'mobilePhone' => $userData['phone'] ?? null,
                'employeeId' => $userData['employee_id'],
                'companyName' => $userData['company_name'] ?? null,
            ];

            Log::info('Creating Azure AD user', ['upn' => $primaryEmail]);

            $response = Http::withToken($token)
                ->post("{$this->graphBaseUrl}/users", $azureUserData);

            if ($response->failed()) {
                $error = $response->json();
                Log::error('Azure user creation failed', [
                    'status' => $response->status(),
                    'error' => $error,
                    'userData' => $azureUserData
                ]);
                
                // Handle specific error cases
                if ($response->status() === 400 && isset($error['error']['message'])) {
                    throw new Exception('Azure AD Error: ' . $error['error']['message']);
                }
                
                throw new Exception('Failed to create user in Azure AD');
            }

            $azureUser = $response->json();
            
            Log::info('Azure AD user created successfully', [
                'azure_id' => $azureUser['id'],
                'upn' => $azureUser['userPrincipalName']
            ]);

            return [
                'azure_id' => $azureUser['id'],
                'azure_upn' => $azureUser['userPrincipalName'],
                'azure_display_name' => $azureUser['displayName'],
            ];

        } catch (Exception $e) {
            Log::error('Azure user creation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update user in Azure AD
     */
    public function updateUser(string $azureId, array $updateData): bool
    {
        try {
            $token = $this->getAccessToken();
            
            $azureUpdateData = array_filter([
                'displayName' => $updateData['name'] ?? null,
                'givenName' => isset($updateData['name']) ? $this->getFirstName($updateData['name']) : null,
                'surname' => isset($updateData['name']) ? $this->getLastName($updateData['name']) : null,
                'mobilePhone' => $updateData['phone'] ?? null,
                'officeLocation' => $updateData['location'] ?? null,
                'jobTitle' => $updateData['job_title'] ?? null,
                'department' => $updateData['department'] ?? null,
            ]);

            $response = Http::withToken($token)
                ->patch("{$this->graphBaseUrl}/users/{$azureId}", $azureUpdateData);

            if ($response->failed()) {
                Log::error('Azure user update failed', [
                    'azure_id' => $azureId,
                    'status' => $response->status(),
                    'error' => $response->json()
                ]);
                throw new Exception('Failed to update user in Azure AD');
            }

            Log::info('Azure AD user updated', ['azure_id' => $azureId]);
            return true;

        } catch (Exception $e) {
            Log::error('Azure user update exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Disable user in Azure AD (blocks sign-in)
     */
    public function disableUser(string $azureId): bool
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->patch("{$this->graphBaseUrl}/users/{$azureId}", [
                    'accountEnabled' => false
                ]);

            if ($response->failed()) {
                Log::error('Azure user disable failed', [
                    'azure_id' => $azureId,
                    'status' => $response->status(),
                    'error' => $response->json()
                ]);
                throw new Exception('Failed to disable user in Azure AD');
            }

            Log::info('Azure AD user disabled', ['azure_id' => $azureId]);
            return true;

        } catch (Exception $e) {
            Log::error('Azure user disable exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Enable user in Azure AD
     */
    public function enableUser(string $azureId): bool
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->patch("{$this->graphBaseUrl}/users/{$azureId}", [
                    'accountEnabled' => true
                ]);

            if ($response->failed()) {
                Log::error('Azure user enable failed', [
                    'azure_id' => $azureId,
                    'status' => $response->status(),
                    'error' => $response->json()
                ]);
                throw new Exception('Failed to enable user in Azure AD');
            }

            Log::info('Azure AD user enabled', ['azure_id' => $azureId]);
            return true;

        } catch (Exception $e) {
            Log::error('Azure user enable exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete user from Azure AD (hard delete)
     */
    public function deleteUser(string $azureId): bool
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->delete("{$this->graphBaseUrl}/users/{$azureId}");

            if ($response->failed()) {
                Log::error('Azure user deletion failed', [
                    'azure_id' => $azureId,
                    'status' => $response->status()
                ]);
                throw new Exception('Failed to delete user from Azure AD');
            }

            Log::info('Azure AD user deleted', ['azure_id' => $azureId]);
            return true;

        } catch (Exception $e) {
            Log::error('Azure user deletion exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get user from Azure AD
     */
    public function getUser(string $azureId): ?array
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->get("{$this->graphBaseUrl}/users/{$azureId}");

            if ($response->failed()) {
                return null;
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Azure get user exception', ['error' => $e->getMessage()]);
            return null;
        }
    }


    /**
 * Find user by User Principal Name (email)
 */
public function findUserByUPN(string $upn): ?array
{
    try {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->get("{$this->graphBaseUrl}/users/{$upn}");

        if ($response->failed()) {
            return null;
        }

        return $response->json();

    } catch (Exception $e) {
        Log::channel('azure')->error('Azure find user by UPN failed', [
            'upn' => $upn,
            'error' => $e->getMessage()
        ]);
        return null;
    }
}

    /**
     * Revoke all user sessions (sign out from all devices)
     */
    public function revokeUserSessions(string $azureId): bool
    {
        try {
            $token = $this->getAccessToken();

            $response = Http::withToken($token)
                ->post("{$this->graphBaseUrl}/users/{$azureId}/revokeSignInSessions");

            if ($response->failed()) {
                Log::error('Azure session revocation failed', [
                    'azure_id' => $azureId,
                    'status' => $response->status()
                ]);
                throw new Exception('Failed to revoke user sessions');
            }

            Log::info('Azure AD user sessions revoked', ['azure_id' => $azureId]);
            return true;

        } catch (Exception $e) {
            Log::error('Azure session revocation exception', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate email in format: firstname.lastname@freight-in-time.com
     * Fallback: employeeID@freight-in-time.com
     */
    private function generateEmail(string $name, string $employeeId): string
    {
        $domain = config('azure.default_domain', 'freight-in-time.com');  
              
        // Clean and format name
        $nameParts = explode(' ', trim($name));
        
        if (count($nameParts) >= 2) {
            $firstName = strtolower($nameParts[0]);
            $lastName = strtolower($nameParts[count($nameParts) - 1]);
            return "{$firstName}.{$lastName}@{$domain}";
        }
        
        // Fallback to employee ID if name is single word
        return strtolower($employeeId) . "@{$domain}";
    }

    /**
     * Generate mail nickname (used for Azure AD)
     */
    private function generateMailNickname(string $name): string
    {
        $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $name);
        return strtolower(substr($cleaned, 0, 64));
    }

    /**
     * Get first name from full name
     */
    private function getFirstName(string $name): string
    {
        $parts = explode(' ', trim($name));
        return $parts[0];
    }

    /**
     * Get last name from full name
     */
    private function getLastName(string $name): string
    {
        $parts = explode(' ', trim($name));
        return count($parts) > 1 ? $parts[count($parts) - 1] : $parts[0];
    }

    /**
     * Generate secure random password
     */
    private function generateSecurePassword(): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*';
        
        $password = '';
        $password .= $uppercase[rand(0, strlen($uppercase) - 1)];
        $password .= $lowercase[rand(0, strlen($lowercase) - 1)];
        $password .= $numbers[rand(0, strlen($numbers) - 1)];
        $password .= $special[rand(0, strlen($special) - 1)];
        
        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < 16; $i++) {
            $password .= $allChars[rand(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    /**
     * Test Azure AD connection
     */
    public function testConnection(): array
    {
        try {
            $token = $this->getAccessToken();
            
            $response = Http::withToken($token)
                ->get("{$this->graphBaseUrl}/organization");

            if ($response->successful()) {
                $org = $response->json();
                return [
                    'success' => true,
                    'message' => 'Successfully connected to Azure AD',
                    'organization' => $org['value'][0]['displayName'] ?? 'Unknown'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to Azure AD',
                'error' => $response->body()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection test failed',
                'error' => $e->getMessage()
            ];
        }
    }
}