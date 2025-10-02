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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
     * Display a listing of users with statistics
     */
    public function index()
    {
        $users = User::with(['company', 'modules', 'roles'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
        ];

        return view('users.index', compact('users', 'stats'));
    }

    /**
     * Get user statistics for dashboard (API endpoint)
     */
    public function stats()
    {
        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
        ];

        return response()->json($stats);
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
                'location' => $request->location,
                'job_title' => $request->job_title,
                'department' => $request->department,
                'company_id' => $request->company_id,
            ];

            // Transform module assignments from form structure to service structure
            $moduleAssignments = [];
            if ($request->has('modules')) {
                // First, process the checkbox arrays
                $processedModules = [];
                
                foreach ($request->modules as $index => $moduleData) {
                    // Handle "all" selections and expand them
                    $locations = $moduleData['location'] ?? [];
                    $moduleIds = $moduleData['module_id'] ?? [];
                    $roleIds = $moduleData['role_id'] ?? [];
                    
                    // Expand "all" into actual values
                    if (is_array($locations) && in_array('all', $locations)) {
                        $locations = Location::pluck('code')->toArray();
                    } elseif ($locations === 'all') {
                        $locations = Location::pluck('code')->toArray();
                    } elseif (!is_array($locations)) {
                        $locations = [$locations]; // Convert single value to array
                    }
                    
                    if (is_array($moduleIds) && in_array('all', $moduleIds)) {
                        $moduleIds = Module::pluck('id')->toArray();
                    } elseif ($moduleIds === 'all') {
                        $moduleIds = Module::pluck('id')->toArray();
                    } elseif (!is_array($moduleIds)) {
                        $moduleIds = [$moduleIds];
                    }
                    
                    if (is_array($roleIds) && in_array('all', $roleIds)) {
                        $roleIds = Role::pluck('id')->toArray();
                    } elseif ($roleIds === 'all') {
                        $roleIds = Role::pluck('id')->toArray();
                    } elseif (!is_array($roleIds)) {
                        $roleIds = [$roleIds];
                    }// Expand "all" into actual values
                    if (is_array($locations) && in_array('all', $locations)) {
                        $locations = Location::pluck('code')->toArray();
                    } elseif ($locations === 'all') {
                        $locations = Location::pluck('code')->toArray();
                    } elseif (!is_array($locations)) {
                        $locations = [$locations]; // Convert single value to array
                    }
                    
                    if (is_array($moduleIds) && in_array('all', $moduleIds)) {
                        $moduleIds = Module::pluck('id')->toArray();
                    } elseif ($moduleIds === 'all') {
                        $moduleIds = Module::pluck('id')->toArray();
                    } elseif (!is_array($moduleIds)) {
                        $moduleIds = [$moduleIds];
                    }
                    
                    if (is_array($roleIds) && in_array('all', $roleIds)) {
                        $roleIds = Role::pluck('id')->toArray();
                    } elseif ($roleIds === 'all') {
                        $roleIds = Role::pluck('id')->toArray();
                    } elseif (!is_array($roleIds)) {
                        $roleIds = [$roleIds];
                    }
                
                    
                    // Create individual assignments for each combination
                    foreach ($moduleIds as $moduleId) {
                        foreach ($roleIds as $roleId) {
                            foreach ($locations as $location) {
                                $processedModules[] = [
                                    'module_id' => $moduleId,
                                    'role_id' => $roleId,
                                    'location' => $location
                                ];
                            }
                        }
                    }
                }

                // NOW use $processedModules to build module assignments
                foreach ($processedModules as $moduleData) {
                    if (!empty($moduleData['module_id']) && !empty($moduleData['role_id'])) {
                        $moduleAssignments[] = [
                            'module_id' => $moduleData['module_id'],
                            'role_id' => $moduleData['role_id'],
                            'location' => $moduleData['location'] ?? $request->location,
                        ];
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
/**
 * Show the form for editing the specified user
 */
public function edit(User $user)
{
    $locations = Location::all();
    $companies = Company::all();
    $modules = Module::with('roles')->get();
    $roles = Role::all();

    // Get user's current module assignments with proper relationship loading
    $user->load(['modules' => function($query) {
        $query->withPivot('role_id', 'location', 'created_at');
    }]);

    $userModuleAssignments = $user->modules->map(function ($module) use ($roles, $locations) {
        $roleId = $module->pivot->role_id;
        $locationCode = $module->pivot->location;
        
        // Find role name
        $role = $roles->firstWhere('id', $roleId);
        $roleName = $role ? $role->name : 'N/A';
        
        // Find location name
        $location = $locations->firstWhere('code', $locationCode);
        $locationName = $location ? $location->name : $locationCode;
        
        return [
            'module_id' => $module->id,
            'module_name' => $module->name,
            'role_id' => $roleId,
            'role_name' => $roleName,
            'location' => $locationCode,
            'location_name' => $locationName,
            'assigned_at' => $module->pivot->created_at ?? now()
        ];
    });

    return view('users.edit', compact(
        'user', 
        'locations', 
        'companies', 
        'modules', 
        'roles', 
        'userModuleAssignments'
    ));
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
                'location' => $request->location,
                // 'job_title' => $request->job_title,
                // 'department' => $request->department,
                'company_id' => $request->company_id,
            ];

            // Update basic user information
            $result = $this->provisioningService->updateUser($user, $updateData);

            // Handle module assignment changes
            if ($request->has('modules')) {
                // Process checkbox arrays FIRST (outside transaction)
                $processedModules = [];
                
                foreach ($request->modules as $index => $moduleData) {
                    // Handle "all" selections and expand them
                    $locations = $moduleData['location'] ?? [];
                    $moduleIds = $moduleData['module_id'] ?? [];
                    $roleIds = $moduleData['role_id'] ?? [];
                    
                    // Expand "all" into actual values
                    if (is_array($locations) && in_array('all', $locations)) {
                        $locations = Location::pluck('code')->toArray();
                    } elseif ($locations === 'all') {
                        $locations = Location::pluck('code')->toArray();
                    } elseif (!is_array($locations)) {
                        $locations = [$locations]; // Convert single value to array
                    }
                    
                    if (is_array($moduleIds) && in_array('all', $moduleIds)) {
                        $moduleIds = Module::pluck('id')->toArray();
                    } elseif ($moduleIds === 'all') {
                        $moduleIds = Module::pluck('id')->toArray();
                    } elseif (!is_array($moduleIds)) {
                        $moduleIds = [$moduleIds];
                    }
                    
                    if (is_array($roleIds) && in_array('all', $roleIds)) {
                        $roleIds = Role::pluck('id')->toArray();
                    } elseif ($roleIds === 'all') {
                        $roleIds = Role::pluck('id')->toArray();
                    } elseif (!is_array($roleIds)) {
                        $roleIds = [$roleIds];
                    }
                                        
                    // Create individual assignments for each combination
                    foreach ($moduleIds as $moduleId) {
                        foreach ($roleIds as $roleId) {
                            foreach ($locations as $location) {
                                $processedModules[] = [
                                    'module_id' => $moduleId,
                                    'role_id' => $roleId,
                                    'location' => $location
                                ];
                            }
                        }
                    }
                }

                // NOW start transaction
                DB::beginTransaction();

                try {
                    // Get current module assignments
                    $currentModules = $user->modules->pluck('id')->toArray();
                    $newModules = collect($processedModules)
                        ->pluck('module_id')
                        ->unique()
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
                    foreach ($processedModules as $moduleData) {
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
     * Disable the specified user via POST request (for AJAX)
     */
    public function disable(User $user)
    {
        try {
            // Check if user is already inactive
            if ($user->status === 'inactive') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already disabled'
                ], 400);
            }

            // Update user status to inactive
            $user->status = 'inactive';
            $user->disabled_at = now();
            $user->save();

            // Try to disable in Azure (optional - don't fail if this fails)
            try {
                if (isset($this->provisioningService)) {
                    $this->provisioningService->disableUser($user);
                }
            } catch (\Exception $e) {
                Log::warning('Azure disable failed but local disable succeeded', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Try to log activity (don't fail if this fails)
            try {
                $this->logUserActivity($user, 'disabled', 'User account disabled');
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User has been disabled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to disable user', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enable a disabled user (local + Azure)
     */
    public function enable(User $user)
    {
        try {
            // Check if user is already active
            if ($user->status === 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already enabled'
                ], 400);
            }

            // Update user status to active
            $user->status = 'active';
            $user->disabled_at = null;
            $user->save();

            // Try to enable in Azure (optional)
            try {
                if (isset($this->provisioningService)) {
                    $this->provisioningService->enableUser($user);
                }
            } catch (\Exception $e) {
                Log::warning('Azure enable failed but local enable succeeded', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Try to log activity (don't fail if this fails)
            try {
                $this->logUserActivity($user, 'enabled', 'User account enabled');
            } catch (\Exception $e) {
                Log::warning('Failed to log user activity', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User has been enabled successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to enable user', [
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to enable user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user status (API endpoint)
     */
    public function getStatus(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'status' => $user->status,
            'disabled_at' => $user->disabled_at,
            'last_login_at' => $user->last_login_at,
            'is_provisioned' => $user->isProvisionedToAzure(),
        ]);
    }

    /**
     * Remove the specified user (soft delete)
     */
    public function destroy(User $user)
    {
        try {
            // For DELETE requests, do a soft delete or redirect
            $user->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
            }

            return redirect()->route('dashboard.index')
                ->with('success', 'User deleted successfully!');

        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete user: ' . $e->getMessage()]);
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
            'performed_by' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}