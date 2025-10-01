<?php

namespace App\Services;

use App\Models\User;
use App\Models\Module;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UserProvisioningService
{
    private AzureService $azureService;
    private ModuleAssignmentService $moduleAssignmentService;

    public function __construct(
        AzureService $azureService,
        ModuleAssignmentService $moduleAssignmentService
    ) {
        $this->azureService = $azureService;
        $this->moduleAssignmentService = $moduleAssignmentService;
    }

    /**
 * Find a user in Azure AD by User Principal Name (UPN / email).
 *
 * @param string $upn
 * @return array|null
 * @throws \Exception
 */
// public function findUserByUPN(string $upn): ?array
// {
//     try {
//         // Query Microsoft Graph for the user by UPN
//         $response = $this->graphClient
//             ->createRequest('GET', '/users/' . urlencode($upn))
//             ->setReturnType(\Microsoft\Graph\Model\User::class)
//             ->execute();

//         if ($response) {
//             return [
//                 'id'               => $response->getId(),
//                 'userPrincipalName'=> $response->getUserPrincipalName(),
//                 'displayName'      => $response->getDisplayName(),
//             ];
//         }

//         return null;
//     } catch (\Exception $e) {
//         // If Azure returns "not found", handle gracefully
//         if (str_contains($e->getMessage(), 'Request_ResourceNotFound')) {
//             return null;
//         }

//         throw $e;
//     }
// }


    /**
     * Provision a new user (create in local DB + Azure + assign modules)
     * 
     * @param array $userData User information
     * @param array $moduleAssignments Array of ['module_id' => X, 'role_id' => Y, 'location' => Z]
     * @return array Provisioning results
     */
    public function provisionUser(array $userData, array $moduleAssignments): array
    {
        $results = [
            'success' => false,
            'user' => null,
            'azure' => null,
            'modules' => [],
            'errors' => [],
            'partial_failure' => false
        ];

        DB::beginTransaction();

        try {
            // Step 1: Create user in local database
            $user = $this->createLocalUser($userData);
            $results['user'] = $user;

            Log::channel('provisioning')->info('Starting user provisioning', [
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'modules_count' => count($moduleAssignments)
            ]);

           // Step 2: Create or update user in Azure AD
try {
    if ($user->azure_id) {
        // Already linked → update existing Azure user
        $this->azureService->updateUser($user->azure_id, [
            'name'       => $user->name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'job_title'  => $user->job_title,
            'department' => $user->department,
        ]);

        $results['azure'] = [
            'success' => true,
            'azure_id' => $user->azure_id,
            'azure_upn' => $user->azure_upn
        ];

        Log::channel('provisioning')->info('User updated in Azure AD', [
            'user_id' => $user->id,
            'azure_id' => $user->azure_id
        ]);
    } else {
        // Try creating a new Azure user
        $azureData = $this->azureService->createUser([
            'name'        => $user->name,
            'employee_id' => $user->employee_id,
            'email'       => $user->email,
            'phone'       => $user->phone,
            'job_title'   => $user->job_title,
            'department'  => $user->department,
            'company_name'=> $user->company->name ?? null,
        ]);

        $user->update([
            'azure_id'          => $azureData['azure_id'],
            'azure_upn'         => $azureData['azure_upn'],
            'azure_display_name'=> $azureData['azure_display_name'],
            'status'            => 'active',
        ]);

        $results['azure'] = [
            'success'  => true,
            'azure_id' => $azureData['azure_id'],
            'azure_upn'=> $azureData['azure_upn']
        ];

        Log::channel('provisioning')->info('User created in Azure AD', [
            'user_id' => $user->id,
            'azure_id' => $azureData['azure_id']
        ]);
    }
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'userPrincipalName already exists')) {
        // Conflict → find and link existing Azure user
        $existing = $this->azureService->findUserByUPN($user->email);

        if ($existing) {
            $user->update([
                'azure_id'          => $existing['id'],
                'azure_upn'         => $existing['userPrincipalName'],
                'azure_display_name'=> $existing['displayName'],
                'status'            => 'active',
            ]);

            $results['azure'] = [
                'success'  => true,
                'azure_id' => $existing['id'],
                'azure_upn'=> $existing['userPrincipalName']
            ];

            Log::channel('provisioning')->info('Linked to existing Azure user', [
                'user_id' => $user->id,
                'azure_id' => $existing['id']
            ]);
        } else {
            $results['azure'] = ['success' => false, 'error' => $e->getMessage()];
            $results['errors'][] = "Azure provisioning failed: " . $e->getMessage();
            DB::rollBack();
            return $results;
        }
    } else {
        $results['azure'] = ['success' => false, 'error' => $e->getMessage()];
        $results['errors'][] = "Azure provisioning failed: " . $e->getMessage();
        DB::rollBack();
        return $results;
    }
}


            // Step 3: Assign modules (Azure Groups + App Roles + External APIs)
            $moduleResults = $this->assignModules($user, $moduleAssignments);
            $results['modules'] = $moduleResults;

            // Check if any module assignments failed
            $failedModules = array_filter($moduleResults, fn($m) => !$m['success']);
            
            if (!empty($failedModules)) {
                $results['partial_failure'] = true;
                $results['errors'][] = count($failedModules) . " module assignment(s) failed";
                
                Log::channel('provisioning')->warning('Partial provisioning failure', [
                    'user_id' => $user->id,
                    'failed_modules' => count($failedModules),
                    'total_modules' => count($moduleAssignments)
                ]);
            }

            // Commit transaction - user created successfully even if some modules failed
            DB::commit();

            $results['success'] = true;

            Log::channel('provisioning')->info('User provisioning completed', [
                'user_id' => $user->id,
                'partial_failure' => $results['partial_failure'],
                'modules_assigned' => count($moduleResults) - count($failedModules),
                'modules_failed' => count($failedModules)
            ]);

            return $results;

        } catch (Exception $e) {
            DB::rollBack();

            $results['errors'][] = $e->getMessage();

            Log::channel('provisioning')->error('User provisioning failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $results;
        }
    }

    /**
     * Create user record in local database
     */
private function createLocalUser(array $userData): User
{
    $user = User::updateOrCreate(
        ['employee_id' => $userData['employee_id']], // lookup key
        [
            'name'       => $userData['name'],
            'email'      => $userData['email'],
            'phone'      => $userData['phone'] ?? null,
            'location'   => $userData['location'] ?? null,
            'job_title'  => $userData['job_title'] ?? null,
            'department' => $userData['department'] ?? null,
            'company_id' => $userData['company_id'],
            'password'   => bcrypt(\Illuminate\Support\Str::random(32)),
            'status'     => 'pending', // will be set to active after Azure provisioning
        ]
    );

    Log::channel('provisioning')->info('Local user record created/updated', [
        'user_id'     => $user->id,
        'employee_id' => $user->employee_id,
        'action'      => $user->wasRecentlyCreated ? 'created' : 'updated'
    ]);

    return $user;
}

    /**
     * Assign all modules to user
     */
    private function assignModules(User $user, array $moduleAssignments): array
    {
        $results = [];

        foreach ($moduleAssignments as $assignment) {
            $moduleId = $assignment['module_id'];
            $roleId = $assignment['role_id'];
            $location = $assignment['location'] ?? null;

            $module = Module::find($moduleId);

            if (!$module) {
                $results[$moduleId] = [
                    'success' => false,
                    'error' => "Module not found: {$moduleId}"
                ];
                continue;
            }

            // Use ModuleAssignmentService to handle the assignment
            $result = $this->moduleAssignmentService->assignUserToModule(
                $user,
                $module,
                $roleId,
                $location
            );

            $results[$moduleId] = array_merge($result, [
                'module_code' => $module->code,
                'module_name' => $module->name
            ]);
        }

        return $results;
    }

    /**
     * Update existing user (local + Azure)
     */
    public function updateUser(User $user, array $updateData): array
    {
        $results = [
            'success' => false,
            'local' => null,
            'azure' => null,
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            // Update local database
            $user->update([
                'name' => $updateData['name'] ?? $user->name,
                'email' => $updateData['email'] ?? $user->email,
                'phone' => $updateData['phone'] ?? $user->phone,
                'job_title' => $updateData['job_title'] ?? $user->job_title,
                'department' => $updateData['department'] ?? $user->department,
            ]);

            $results['local'] = ['success' => true];

            // Update Azure if user is provisioned
            if ($user->isProvisionedToAzure()) {
                try {
                    $this->azureService->updateUser($user->azure_id, $updateData);
                    $results['azure'] = ['success' => true];

                    Log::channel('provisioning')->info('User updated in Azure', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id
                    ]);

                } catch (Exception $e) {
                    $results['azure'] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                    $results['errors'][] = "Azure update failed: " . $e->getMessage();
                }
            }

            DB::commit();
            $results['success'] = true;

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();

            Log::channel('provisioning')->error('User update failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return $results;
        }
    }

    /**
     * Disable user (local + Azure)
     */
    public function disableUser(User $user): array
    {
        $results = [
            'success' => false,
            'local' => null,
            'azure' => null,
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            // Disable in local database
            $user->update(['status' => 'inactive']);
            $results['local'] = ['success' => true];

            // Disable in Azure
            if ($user->isProvisionedToAzure()) {
                try {
                    $this->azureService->disableUser($user->azure_id);
                    $results['azure'] = ['success' => true];

                    Log::channel('provisioning')->info('User disabled in Azure', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id
                    ]);

                } catch (Exception $e) {
                    $results['azure'] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                    $results['errors'][] = "Azure disable failed: " . $e->getMessage();
                }
            }

            DB::commit();
            $results['success'] = true;

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();

            Log::channel('provisioning')->error('User disable failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return $results;
        }
    }

    /**
     * Enable user (local + Azure)
     */
    public function enableUser(User $user): array
    {
        $results = [
            'success' => false,
            'local' => null,
            'azure' => null,
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            // Enable in local database
            $user->update(['status' => 'active']);
            $results['local'] = ['success' => true];

            // Enable in Azure
            if ($user->isProvisionedToAzure()) {
                try {
                    $this->azureService->enableUser($user->azure_id);
                    $results['azure'] = ['success' => true];

                    Log::channel('provisioning')->info('User enabled in Azure', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id
                    ]);

                } catch (Exception $e) {
                    $results['azure'] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                    $results['errors'][] = "Azure enable failed: " . $e->getMessage();
                }
            }

            DB::commit();
            $results['success'] = true;

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();

            Log::channel('provisioning')->error('User enable failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return $results;
        }
    }

    /**
     * Delete user (local + Azure)
     */
    public function deleteUser(User $user, bool $hardDelete = false): array
    {
        $results = [
            'success' => false,
            'local' => null,
            'azure' => null,
            'errors' => []
        ];

        DB::beginTransaction();

        try {
            // Delete from Azure first
            if ($user->isProvisionedToAzure() && $user->canDeleteFromAzure()) {
                try {
                    $this->azureService->deleteUser($user->azure_id);
                    $results['azure'] = ['success' => true];

                    Log::channel('provisioning')->info('User deleted from Azure', [
                        'user_id' => $user->id,
                        'azure_id' => $user->azure_id
                    ]);

                } catch (Exception $e) {
                    $results['azure'] = [
                        'success' => false,
                        'error' => $e->getMessage()
                    ];
                    $results['errors'][] = "Azure deletion failed: " . $e->getMessage();
                }
            }

            // Delete from local database
            if ($hardDelete) {
                $user->forceDelete(); // Permanent deletion
            } else {
                $user->delete(); // Soft delete if configured
            }

            $results['local'] = ['success' => true];

            DB::commit();
            $results['success'] = true;

            return $results;

        } catch (Exception $e) {
            DB::rollBack();
            $results['errors'][] = $e->getMessage();

            Log::channel('provisioning')->error('User deletion failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return $results;
        }
    }

    /**
     * Retry failed module assignments
     */
    public function retryFailedModuleAssignments(User $user): array
    {
        $failedAssignments = $user->getFailedModuleAssignments();
        $results = [];

        foreach ($failedAssignments as $failedModule) {
            $module = Module::find($failedModule->pivot->module_id);
            
            if (!$module) {
                continue;
            }

            $result = $this->moduleAssignmentService->assignUserToModule(
                $user,
                $module,
                $failedModule->pivot->role_id,
                $failedModule->pivot->location
            );

            $results[$module->id] = array_merge($result, [
                'module_code' => $module->code,
                'retry_attempt' => true
            ]);
        }

        Log::channel('provisioning')->info('Retried failed module assignments', [
            'user_id' => $user->id,
            'modules_retried' => count($results)
        ]);

        return $results;
    }

    /**
     * Get provisioning status for a user
     */
    public function getProvisioningStatus(User $user): array
    {
        return $user->getProvisioningStatus();
    }

    /**
     * Bulk provision users
     */
   public function bulkProvisionUsers(array $usersData): array
{
    $results = [
        'total' => count($usersData),
        'successful' => 0,
        'failed' => 0,
        'partial' => 0,
        'created' => 0,
        'updated' => 0,
        'details' => []
    ];

    foreach ($usersData as $userData) {
        $moduleAssignments = $userData['modules'] ?? [];
        unset($userData['modules']);

        $result = $this->provisionUser($userData, $moduleAssignments);

        if ($result['user']) {
            if ($result['user']->wasRecentlyCreated) {
                $results['created']++;
            } else {
                $results['updated']++;
            }
        }

        if ($result['success']) {
            if ($result['partial_failure']) {
                $results['partial']++;
            } else {
                $results['successful']++;
            }
        } else {
            $results['failed']++;
        }

        $results['details'][] = [
            'employee_id' => $userData['employee_id'],
            'name' => $userData['name'],
            'action' => $result['user']->wasRecentlyCreated ? 'created' : 'updated',
            'result' => $result
        ];
    }

    Log::channel('provisioning')->info('Bulk provisioning completed', $results);

    return $results;
}

}