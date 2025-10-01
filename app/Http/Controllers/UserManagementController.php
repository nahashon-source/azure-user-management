<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 

use App\Models\User;
use App\Models\Company;
use App\Models\Location;
use App\Models\Module;
use App\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserProvisioningService;
use App\Services\ModuleAssignmentService;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    private UserProvisioningService $provisioningService;
    private ModuleAssignmentService $moduleAssignmentService;

    public function __construct(
        UserProvisioningService $provisioningService,
        ModuleAssignmentService $moduleAssignmentService
    ) {
        $this->provisioningService = $provisioningService;
        $this->moduleAssignmentService = $moduleAssignmentService;
    }

    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::with(['company', 'modules', 'roles'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $locations = Location::all();
        $companies = Company::all();
        $modules = Module::with('roles')->get();
        $roles = Role::all();

        return view('users.create', compact('locations', 'companies', 'modules', 'roles'));
    }

    /**
     * Store a newly created user (with Azure provisioning)
     */
   public function store(StoreUserRequest $request)
{
    try {
        // Prepare user data
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'employee_id' => $request->employee_id,
            'phone' => $request->phone,
            'location' => $request->location, // ADDED: Missing location field
            'job_title' => $request->job_title,
            'department' => $request->department,
            'company_id' => $request->company_id,
        ];

        // Transform module assignments from form structure to service structure
        $moduleAssignments = [];
        if ($request->has('modules')) {
            foreach ($request->modules as $moduleCode => $moduleData) {
                // Check if module is enabled
                if (!empty($moduleData['enabled'])) {
                    // Get module by code
                    $module = Module::where('code', $moduleCode)->first();
                    
                    if ($module && !empty($moduleData['role_ids'])) {
                        // Create assignment for each selected role
                        foreach ($moduleData['role_ids'] as $roleId) {
                            $moduleAssignments[] = [
                                'module_id' => $module->id,
                                'role_id' => $roleId,
                                'location' => $request->location, // Use user's location
                            ];
                        }
                    }
                }
            }
        }

        // Validate that at least one module is assigned
        if (empty($moduleAssignments)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one module with a role must be assigned'
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['modules' => 'At least one module with a role must be assigned']);
        }

        // Provision user through service
        $result = $this->provisioningService->provisionUser($userData, $moduleAssignments);

        if ($result['success']) {
            $user = $result['user'];
            
            // Log audit trail
            $this->logUserActivity($user, 'created', 'User account created and provisioned');

            // Prepare success message
            $message = 'User created successfully!';
            
            if ($result['partial_failure']) {
                $failedCount = count(array_filter($result['modules'], fn($m) => !$m['success']));
                $message .= " Warning: {$failedCount} module assignment(s) failed.";
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'user' => $user->load(['company', 'modules.roles']),
                    'provisioning_details' => [
                        'azure' => $result['azure'],
                        'modules' => $result['modules'],
                        'partial_failure' => $result['partial_failure']
                    ]
                ]);
            }

            $flashType = $result['partial_failure'] ? 'warning' : 'success';
            return redirect()->route('dashboard.index')
                ->with($flashType, $message);

        } else {
            // Complete failure
            $errorMessage = 'Failed to create user: ' . implode(', ', $result['errors']);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $result['errors']
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $errorMessage]);
        }

    } catch (\Exception $e) {
        Log::error('User creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ], 500);
        }

        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()]);
    }
}
    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['company', 'modules.roles', 'roles']);
        
        // Get provisioning status
        $provisioningStatus = $this->provisioningService->getProvisioningStatus($user);
        
        return view('users.show', compact('user', 'provisioningStatus'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $locations = Location::all();
        $companies = Company::all();
        $modules = Module::with('roles')->get();
        $roles = Role::all();

        // Get user's current module assignments
        $userModules = $user->modules;

        return view('users.edit', compact('user', 'locations', 'companies', 'modules', 'roles', 'userModules'));
    }

    /**
     * Update the specified user
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            // Prepare update data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'phone' => $request->phone,
                'job_title' => $request->job_title,
                'department' => $request->department,
                'company_id' => $request->company_id,
            ];

            // Update basic user information
            $result = $this->provisioningService->updateUser($user, $updateData);

            // Handle module assignment changes
            if ($request->has('modules')) {
                DB::beginTransaction();

                try {
                    // Get current module assignments
                    $currentModules = $user->modules->pluck('id')->toArray();
                    $newModules = collect($request->modules)
                        ->pluck('module_id')
                        ->filter()
                        ->toArray();

                    // Find modules to remove
                    $modulesToRemove = array_diff($currentModules, $newModules);
                    foreach ($modulesToRemove as $moduleId) {
                        $module = Module::find($moduleId);
                        if ($module) {
                            $this->moduleAssignmentService->removeUserFromModule($user, $module);
                        }
                    }

                    // Find modules to add or update
                    foreach ($request->modules as $moduleData) {
                        if (!empty($moduleData['module_id']) && !empty($moduleData['role_id'])) {
                            $module = Module::find($moduleData['module_id']);
                            if ($module) {
                                // Check if already assigned
                                $existingAssignment = $user->modules()
                                    ->where('module_id', $moduleData['module_id'])
                                    ->first();

                                if ($existingAssignment) {
                                    // Update existing assignment if role or location changed
                                    $pivot = $existingAssignment->pivot;
                                    if ($pivot->role_id != $moduleData['role_id'] || 
                                        $pivot->location != ($moduleData['location'] ?? null)) {
                                        
                                        // Re-assign with new details
                                        $this->moduleAssignmentService->assignUserToModule(
                                            $user,
                                            $module,
                                            $moduleData['role_id'],
                                            $moduleData['location'] ?? null
                                        );
                                    }
                                } else {
                                    // New module assignment
                                    $this->moduleAssignmentService->assignUserToModule(
                                        $user,
                                        $module,
                                        $moduleData['role_id'],
                                        $moduleData['location'] ?? null
                                    );
                                }
                            }
                        }
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            $this->logUserActivity($user, 'updated', 'User account updated');

            $message = 'User updated successfully!';
            if (!empty($result['errors'])) {
                $message .= ' Some updates may have failed.';
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'user' => $user->fresh()->load(['company', 'modules']),
                    'update_details' => $result
                ]);
            }

            return redirect()->route('dashboard.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    /**
     * Disable the specified user (local + Azure)
     */
    public function destroy(User $user)
    {
        try {
            $result = $this->provisioningService->disableUser($user);

            $this->logUserActivity($user, 'disabled', 'User account disabled');

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User disabled successfully',
                    'details' => $result
                ]);
            }

            return redirect()->route('dashboard.index')
                ->with('success', 'User disabled successfully!');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to disable user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to disable user: ' . $e->getMessage()]);
        }
    }

    /**
     * Enable a disabled user (local + Azure)
     */
    public function enable(User $user)
    {
        try {
            $result = $this->provisioningService->enableUser($user);

            $this->logUserActivity($user, 'enabled', 'User account enabled');

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User enabled successfully',
                    'details' => $result
                ]);
            }

            return redirect()->back()
                ->with('success', 'User enabled successfully!');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to enable user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to enable user: ' . $e->getMessage()]);
        }
    }

    /**
     * Retry failed module assignments for a user
     */
    public function retryModuleAssignments(User $user)
    {
        try {
            $results = $this->provisioningService->retryFailedModuleAssignments($user);

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $totalCount = count($results);

            $message = "Retried {$totalCount} module(s): {$successCount} succeeded.";

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'results' => $results
                ]);
            }

            return redirect()->back()
                ->with('success', $message);

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to retry module assignments: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to retry: ' . $e->getMessage()]);
        }
    }

    /**
     * Log user activity for audit trail
     */
    private function logUserActivity(User $user, string $action, string $description)
    {
        $user->auditLogs()->create([
            'action' => $action,
            'description' => $description,
            'performed_by' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}