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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
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
     * Store a newly created user
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            // Create the user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'phone' => $request->phone,
                'location' => $request->location,
                'company_id' => $request->company_id,
                'status' => 'pending',
                'password' => Hash::make('temp_password_123'),
            ]);

            // Handle module assignments (new format: [location, module_id, role_id])
            if ($request->has('modules')) {
                foreach ($request->modules as $moduleData) {
                    if (!empty($moduleData['module_id']) && !empty($moduleData['role_id'])) {
                        $user->modules()->attach($moduleData['module_id'], [
                            'role_id' => $moduleData['role_id'],
                            'location' => $moduleData['location'] ?? null,
                            'assigned_at' => now(),
                        ]);
                    }
                }
            }

            $this->logUserActivity($user, 'created', 'User account created');
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully',
                    'user' => $user->load(['company', 'modules'])
                ]);
            }

            return redirect()->route('dashboard.index')
                ->with('success', 'User created successfully!');

        } catch (\Exception $e) {
            DB::rollback();

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
        
        return view('users.show', compact('user'));
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
            DB::beginTransaction();

            // Update user basic information
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'employee_id' => $request->employee_id,
                'phone' => $request->phone,
                'location' => $request->location,
                'company_id' => $request->company_id,
            ]);

            // Clear old module assignments
            $user->modules()->detach();

            // Reassign new modules
            if ($request->has('modules')) {
                foreach ($request->modules as $moduleData) {
                    if (!empty($moduleData['module_id']) && !empty($moduleData['role_id'])) {
                        $user->modules()->attach($moduleData['module_id'], [
                            'role_id' => $moduleData['role_id'],
                            'location' => $moduleData['location'] ?? null,
                            'assigned_at' => now(),
                        ]);
                    }
                }
            }

            $this->logUserActivity($user, 'updated', 'User account updated');
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully',
                    'user' => $user->load(['company', 'modules'])
                ]);
            }

            return redirect()->route('dashboard.index')
                ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();

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
     * Remove (disable) the specified user
     */
    public function destroy(User $user)
    {
        try {
            $user->update([
                'status' => 'inactive',
                'disabled_at' => now()
            ]);

            $this->logUserActivity($user, 'disabled', 'User account disabled');

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User disabled successfully'
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
     * Enable a disabled user
     */
    public function enable(User $user)
    {
        try {
            $user->update([
                'status' => 'active',
                'disabled_at' => null
            ]);

            $this->logUserActivity($user, 'enabled', 'User account enabled');

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User enabled successfully'
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
