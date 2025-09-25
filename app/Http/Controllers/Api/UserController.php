<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;

class UserController extends Controller
{
    /**
     * Get paginated users with filters
     */
    public function index(Request $request)
    {
        $query = User::with(['company', 'modules']);

        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('employee_id', 'LIKE', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Apply location filter
        if ($request->has('location') && $request->location) {
            $query->where('location', $request->location);
        }

        // Apply module filter
        if ($request->has('module') && $request->module) {
            $query->whereHas('modules', function ($q) use ($request) {
                $q->where('code', $request->module);
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        // Format the response
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'phone' => $user->phone,
                'location' => ucfirst($user->location),
                'status' => $user->status,
                'company' => $user->company ? [
                    'id' => $user->company->id,
                    'name' => $user->company->name
                ] : null,
                'modules' => $user->modules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'name' => $module->name,
                        'code' => $module->code
                    ];
                }),
                'last_login_at' => $user->last_login_at ? $user->last_login_at->diffForHumans() : null,
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'data' => $users->items(),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total()
        ]);
    }

    /**
     * Get single user details
     */
    public function show(User $user)
    {
        $user->load(['company', 'modules.roles', 'roles']);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'phone' => $user->phone,
            'location' => ucfirst($user->location),
            'status' => $user->status,
            'company' => $user->company ? [
                'id' => $user->company->id,
                'name' => $user->company->name
            ] : null,
            'modules' => $user->modules->map(function ($module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'code' => $module->code,
                    'role' => $module->pivot->role ?? null
                ];
            }),
            'last_login_at' => $user->last_login_at ? $user->last_login_at->diffForHumans() : null,
            'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $user->updated_at->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Delete (disable) user
     */
    public function destroy(User $user)
    {
        try {
            $user->update([
                'status' => 'inactive',
                'disabled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User disabled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disable user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get companies by location
     */
    public function getCompaniesByLocation(string $location)
    {
        $companies = Company::where('location', $location)
            ->select('id', 'name', 'location')
            ->orderBy('name')
            ->get();

        return response()->json($companies);
    }

    /**
     * Search users (for autocomplete)
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->orWhere('employee_id', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'email', 'employee_id')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    /**
     * Get user statistics
     */
    public function getStats()
    {
        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'pending' => User::where('status', 'pending')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
        ];

        return response()->json($stats);
    }
}