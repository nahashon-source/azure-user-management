<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use App\Models\UserModule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ModuleAssignmentService
{
    private AzureService $azureService;

    public function __construct(AzureService $azureService)
    {
        $this->azureService = $azureService;
    }

    /**
     * Assign user to a module (handles Azure + External API provisioning)
     * 
     * @param User $user
     * @param Module $module
     * @param int $roleId
     * @param string|null $location
     * @return array Status of assignment
     */
   public function assignUserToModule(User $user, Module $module, int $roleId, ?string $location = null): array
{
    $results = [
        'success' => false,
        'azure_group' => ['success' => false, 'skipped' => true],
        'azure_app_role' => ['success' => false, 'skipped' => true],
        'external_api' => ['success' => false, 'skipped' => true],
        'errors' => []
    ];

    try {
        // Validate location if required
        if ($module->requires_location && empty($location)) {
            throw new Exception("Module {$module->code} requires a location");
        }

        $userModule = $this->createPivotRecord($user, $module, $roleId, $location);

        // Azure AD Group Assignment
        if ($module->requiresGroupAssignment()) {
            $results['azure_group'] = $this->assignToAzureGroup($user, $module, $userModule);
            if (!$results['azure_group']['success']) {
                $results['errors'][] = "Azure Group: " . ($results['azure_group']['error'] ?? 'Unknown error');
            }
        }

        // Azure App Role Assignment
        if ($module->requiresAppRoleAssignment()) {
            $results['azure_app_role'] = $this->assignAzureAppRole($user, $module, $userModule);
            if (!$results['azure_app_role']['success']) {
                $results['errors'][] = "App Role: " . ($results['azure_app_role']['error'] ?? 'Unknown error');
            }
        }

        // External API Provisioning
        if ($module->hasExternalApi()) {
            $results['external_api'] = $this->provisionToExternalSystem($user, $module, $userModule, $location);
            if (!$results['external_api']['success']) {
                $results['errors'][] = "External API: " . ($results['external_api']['error'] ?? 'Unknown error');
            }
        }

        // Determine overall success - Azure operations are critical, external API is not
        $azureSuccess = (!$module->requiresGroupAssignment() || $results['azure_group']['success']) &&
                       (!$module->requiresAppRoleAssignment() || $results['azure_app_role']['success']);
        
        $results['success'] = $azureSuccess; // External API failure doesn't block overall success

        // Update sync status based on results
        if ($results['external_api']['success']) {
            $userModule->markAsSynced($results['external_api']['external_system_id'] ?? null);
        } elseif ($module->hasExternalApi()) {
            // Azure succeeded but external failed - mark as partial
            $userModule->markAsFailed($results['external_api']['error'] ?? 'External API provisioning failed');
        } else {
            // No external API, just Azure - mark as synced
            $userModule->markAsSynced(null);
        }

        Log::channel('provisioning')->info('Module assignment completed', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'overall_success' => $results['success'],
            'results' => $results
        ]);

        return $results;

    } catch (Exception $e) {
        Log::channel('provisioning')->error('Module assignment exception', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $results['errors'][] = $e->getMessage();
        $results['success'] = false;
        
        if (isset($userModule)) {
            $userModule->markAsFailed($e->getMessage());
        }
        
        return $results;
    }
}

    /**
     * Create pivot record in user_modules table
     */
    private function createPivotRecord(User $user, Module $module, int $roleId, ?string $location): UserModule
    {
        $userModule = UserModule::updateOrCreate(
            [
                'user_id' => $user->id,
                'module_id' => $module->id,
            ],
            [
                'role_id' => $roleId,
                'location' => $location,
                'assigned_at' => now(),
                'external_sync_status' => 'pending',
            ]
        );

        Log::channel('provisioning')->info('User module pivot record created', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'user_module_id' => $userModule->id
        ]);

        return $userModule;
    }

    /**
     * Assign user to Azure AD Security Group
     */
 

    /**
 * Assign user to Azure AD Security Group (role-based)
 */
private function assignToAzureGroup(User $user, Module $module, UserModule $userModule): array
{
    try {
        // Get the correct Azure group based on the user's role
        $roleId = $userModule->role_id;
        $groupInfo = $module->getAzureGroupForRole($roleId);

        if (!$groupInfo) {
            throw new Exception("No Azure group mapping found for module '{$module->name}' and role ID {$roleId}");
        }

        if (!$user->isProvisionedToAzure()) {
            throw new Exception("User {$user->name} is not provisioned to Azure AD");
        }

        $azureGroupId = $groupInfo['group_id'];
        $azureGroupName = $groupInfo['group_name'];

        $token = $this->azureService->getAccessToken();
        $graphUrl = config('azure.graph_api_base_url');

        Log::channel('azure')->info('Attempting Azure group assignment', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'module' => $module->name,
            'role_id' => $roleId,
            'azure_group_id' => $azureGroupId,
            'azure_group_name' => $azureGroupName
        ]);

        $response = Http::withToken($token)
            ->post("{$graphUrl}/groups/{$azureGroupId}/members/\$ref", [
                '@odata.id' => "{$graphUrl}/directoryObjects/{$user->azure_id}"
            ]);

        if ($response->failed()) {
            // Check if already a member (409 conflict)
            if ($response->status() === 409) {
                Log::channel('azure')->info('User already member of Azure Group', [
                    'user_id' => $user->id,
                    'group_id' => $azureGroupId,
                    'group_name' => $azureGroupName
                ]);
                return ['success' => true, 'status' => 'already_member', 'group_name' => $azureGroupName];
            }

                // Handle "already exists" error from Microsoft Graph API
                if ($response->status() === 400) {
                    $errorBody = $response->json();
                    if (isset($errorBody['error']['message']) && 
                        str_contains($errorBody['error']['message'], 'already exist')) {
                        Log::channel('azure')->info('User already member of Azure Group', [
                            'user_id' => $user->id,
                            'group_id' => $azureGroupId,
                            'group_name' => $azureGroupName
                        ]);
                        return ['success' => true, 'status' => 'already_member', 'group_name' => $azureGroupName];
                    }
                }

            throw new Exception("Failed to add user to Azure Group '{$azureGroupName}': " . $response->body());
        }

        Log::channel('azure')->info('User added to Azure Group successfully', [
            'user_id' => $user->id,
            'azure_id' => $user->azure_id,
            'group_id' => $azureGroupId,
            'group_name' => $azureGroupName,
            'module' => $module->code,
            'role_id' => $roleId
        ]);

        return [
            'success' => true, 
            'status' => 'assigned',
            'group_name' => $azureGroupName,
            'group_id' => $azureGroupId
        ];

    } catch (Exception $e) {
        Log::channel('azure')->error('Azure Group assignment failed', [
            'user_id' => $user->id,
            'module_id' => $module->id,
            'role_id' => $userModule->role_id ?? null,
            'error' => $e->getMessage()
        ]);

        return ['success' => false, 'error' => $e->getMessage()];
    }
}

    /**
     * Assign Azure Enterprise App Role to user
     */
    private function assignAzureAppRole(User $user, Module $module, UserModule $userModule): array
    {
        try {
            if (!$module->hasAzureAppConfigured()) {
                throw new Exception("Module {$module->code} does not have Azure App ID configured");
            }

            if (!$user->isProvisionedToAzure()) {
                throw new Exception("User {$user->name} is not provisioned to Azure AD");
            }

            $token = $this->azureService->getAccessToken();
            $graphUrl = config('azure.graph_api_base_url');

            // Get default app role (or specific role based on user's role_id)
            $appRoleId = $this->getAppRoleId($module, $userModule->role_id);

            $response = Http::withToken($token)
                ->post("{$graphUrl}/servicePrincipals/{$module->azure_enterprise_app_id}/appRoleAssignments", [
                    'principalId' => $user->azure_id,
                    'resourceId' => $module->azure_enterprise_app_id,
                    'appRoleId' => $appRoleId
                ]);

            if ($response->failed()) {
                throw new Exception("Failed to assign app role: " . $response->body());
            }

            Log::channel('azure')->info('App role assigned', [
                'user_id' => $user->id,
                'azure_id' => $user->azure_id,
                'app_id' => $module->azure_enterprise_app_id,
                'module' => $module->code
            ]);

            return ['success' => true, 'status' => 'assigned'];

        } catch (Exception $e) {
            Log::channel('azure')->error('App role assignment failed', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Provision user to external system API (SCM, FITGP, BIZ)
     */
    private function provisionToExternalSystem(User $user, Module $module, UserModule $userModule, ?string $location): array
    {
        try {
            $apiEndpoint = $module->external_api_endpoint;
            $authMethod = $module->api_auth_method;

            if (empty($apiEndpoint)) {
                throw new Exception("Module {$module->code} does not have external API endpoint configured");
            }

            // Prepare request payload
            $payload = $this->buildExternalApiPayload($user, $module, $location);

            // Build HTTP request with authentication
            $request = $this->buildAuthenticatedRequest($module);

            Log::channel('provisioning')->info('Sending user to external API', [
                'user_id' => $user->id,
                'module' => $module->code,
                'endpoint' => $apiEndpoint,
                'auth_method' => $authMethod
            ]);

            $response = $request->post($apiEndpoint, $payload);

            if ($response->failed()) {
                throw new Exception("External API request failed: {$response->status()} - {$response->body()}");
            }

            $responseData = $response->json();
            
            // Extract external system ID from response
            $externalSystemId = $this->extractExternalUserId($responseData, $module);

            Log::channel('provisioning')->info('User provisioned to external system', [
                'user_id' => $user->id,
                'module' => $module->code,
                'external_system_id' => $externalSystemId
            ]);

            // Update pivot with external system ID
            $userModule->update([
                'external_system_id' => $externalSystemId,
            ]);

            return [
                'success' => true,
                'status' => 'provisioned',
                'external_system_id' => $externalSystemId
            ];

        } catch (Exception $e) {
            Log::channel('provisioning')->error('External API provisioning failed', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Build authenticated HTTP request based on module configuration
     */
    private function buildAuthenticatedRequest(Module $module)
    {
        $request = Http::timeout(30)->acceptJson();

        $authMethod = $module->api_auth_method;
        $credentials = $module->getApiCredentials();

        switch ($authMethod) {
            case 'bearer':
                if (isset($credentials['token'])) {
                    $request = $request->withToken($credentials['token']);
                }
                break;

            case 'api_key':
                if (isset($credentials['api_key'])) {
                    $request = $request->withHeaders([
                        'X-API-Key' => $credentials['api_key']
                    ]);
                }
                break;

            case 'oauth':
                // OAuth implementation would go here
                // This would require a separate method to handle OAuth token exchange
                break;

            case 'none':
            default:
                // No authentication
                break;
        }

        return $request;
    }

    /**
     * Build payload for external API
     */
    private function buildExternalApiPayload(User $user, Module $module, ?string $location): array
    {
        // Base payload structure
        $payload = [
            'employee_id' => $user->employee_id,
            'name' => $user->name,
            'email' => $user->email,
            'azure_upn' => $user->azure_upn,
            'phone' => $user->phone,
            'location' => $location,
            'module_code' => $module->code,
            'company_id' => $user->company_id,
        ];

        // Add module-specific fields based on module code
        switch ($module->code) {
            case 'SCM':
                // Add SCM-specific fields
                break;

            case 'FITGP':
                // Add FITGP-specific fields
                break;

            case 'BIZ':
                // Add BIZ-specific fields
                break;
        }

        return $payload;
    }

    /**
     * Extract external user ID from API response
     */
    private function extractExternalUserId(array $response, Module $module): ?string
    {
        // Try common field names
        $possibleFields = ['id', 'user_id', 'userId', 'external_id', 'externalId'];

        foreach ($possibleFields as $field) {
            if (isset($response[$field])) {
                return (string) $response[$field];
            }
        }

        // Module-specific extraction logic
        switch ($module->code) {
            case 'SCM':
                // SCM-specific ID extraction
                return $response['scm_user_id'] ?? null;

            case 'FITGP':
                // FITGP-specific ID extraction
                return $response['fitgp_id'] ?? null;

            case 'BIZ':
                // BIZ-specific ID extraction
                return $response['biz_user_id'] ?? null;
        }

        return null;
    }

    /**
     * Get App Role ID based on module and user role
     */
    private function getAppRoleId(Module $module, int $roleId): string
{
    try {
        $token = $this->azureService->getAccessToken();
        $graphUrl = config('azure.graph_api_base_url');

        // Get service principal (enterprise app) details
        $response = Http::withToken($token)
            ->get("{$graphUrl}/servicePrincipals/{$module->azure_enterprise_app_id}");

        if ($response->failed()) {
            throw new Exception("Failed to fetch app roles");
        }

        $appRoles = $response->json()['appRoles'] ?? [];
        
        // Get role name from database
        $role = \App\Models\Role::find($roleId);
        $targetRoleName = "{$module->code}.{$role->code}"; // e.g., "SCM.Admin"

        // Find matching app role
        foreach ($appRoles as $appRole) {
            if ($appRole['displayName'] === $targetRoleName || $appRole['value'] === $targetRoleName) {
                return $appRole['id'];
            }
        }

        // If no match found, use default "User" role
        foreach ($appRoles as $appRole) {
            if ($appRole['value'] === 'User' || $appRole['displayName'] === 'User') {
                Log::channel('azure')->warning("Exact role not found, using default User role", [
                    'module' => $module->code,
                    'requested_role' => $targetRoleName
                ]);
                return $appRole['id'];
            }
        }

        throw new Exception("No suitable app role found for module {$module->code}");

    } catch (Exception $e) {
        Log::channel('azure')->error('Failed to determine app role ID', [
            'module_id' => $module->id,
            'role_id' => $roleId,
            'error' => $e->getMessage()
        ]);
        
        // Return default as fallback
        return config('azure.default_app_role_id', '00000000-0000-0000-0000-000000000000');
    }
}

    /**
     * Remove user from module (unassign from Azure + External system)
     */
    public function removeUserFromModule(User $user, Module $module): array
    {
        $results = [
            'success' => false,
            'azure_group' => null,
            'azure_app_role' => null,
            'external_api' => null,
            'errors' => []
        ];

        try {
            // Remove from Azure Group
            if ($module->requiresGroupAssignment() && $user->isProvisionedToAzure()) {
                $results['azure_group'] = $this->removeFromAzureGroup($user, $module);
            }

            // Remove App Role assignment
            if ($module->requiresAppRoleAssignment() && $user->isProvisionedToAzure()) {
                $results['azure_app_role'] = $this->removeAzureAppRole($user, $module);
            }

            // Note: External API deprovisioning would require API support
            // Most external systems don't support user deletion via API

            // Delete pivot record
            UserModule::where('user_id', $user->id)
                ->where('module_id', $module->id)
                ->delete();

            $results['success'] = empty($results['errors']);

            return $results;

        } catch (Exception $e) {
            Log::channel('provisioning')->error('Module removal exception', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'error' => $e->getMessage()
            ]);

            $results['errors'][] = $e->getMessage();
            return $results;
        }
    }

    /**
     * Remove user from Azure AD Security Group
     */
    private function removeFromAzureGroup(User $user, Module $module): array
    {
        try {
            $token = $this->azureService->getAccessToken();
            $graphUrl = config('azure.graph_api_base_url');

            $response = Http::withToken($token)
                ->delete("{$graphUrl}/groups/{$module->azure_group_id}/members/{$user->azure_id}/\$ref");

            if ($response->failed() && $response->status() !== 404) {
                throw new Exception("Failed to remove user from Azure Group: " . $response->body());
            }

            Log::channel('azure')->info('User removed from Azure Group', [
                'user_id' => $user->id,
                'group_id' => $module->azure_group_id
            ]);

            return ['success' => true, 'status' => 'removed'];

        } catch (Exception $e) {
            Log::channel('azure')->error('Azure Group removal failed', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove Azure App Role assignment
     */
    private function removeAzureAppRole(User $user, Module $module): array
    {
        try {
            $token = $this->azureService->getAccessToken();
            $graphUrl = config('azure.graph_api_base_url');

            // First, get the assignment ID
            $response = Http::withToken($token)
                ->get("{$graphUrl}/users/{$user->azure_id}/appRoleAssignments");

            if ($response->failed()) {
                throw new Exception("Failed to retrieve app role assignments");
            }

            $assignments = $response->json()['value'] ?? [];
            
            // Find assignment for this specific app
            foreach ($assignments as $assignment) {
                if ($assignment['resourceId'] === $module->azure_enterprise_app_id) {
                    $assignmentId = $assignment['id'];
                    
                    // Delete the assignment
                    $deleteResponse = Http::withToken($token)
                        ->delete("{$graphUrl}/users/{$user->azure_id}/appRoleAssignments/{$assignmentId}");

                    if ($deleteResponse->failed()) {
                        throw new Exception("Failed to remove app role assignment");
                    }

                    Log::channel('azure')->info('App role assignment removed', [
                        'user_id' => $user->id,
                        'app_id' => $module->azure_enterprise_app_id
                    ]);

                    return ['success' => true, 'status' => 'removed'];
                }
            }

            return ['success' => true, 'status' => 'not_found'];

        } catch (Exception $e) {
            Log::channel('azure')->error('App role removal failed', [
                'user_id' => $user->id,
                'module_id' => $module->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}