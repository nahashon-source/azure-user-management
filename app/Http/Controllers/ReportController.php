<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Module;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display reports dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_companies' => Company::count(),
            'total_modules' => Module::count(),
        ];

        return view('reports.index', compact('stats'));
    }

    /**
     * Generate user reports
     */
    public function users(Request $request)
    {
        $query = User::with(['company', 'modules']);

        // Apply filters
        if ($request->location) {
            $query->where('location', $request->location);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->company_id) {
            $query->where('company_id', $request->company_id);
        }

        $users = $query->get();

        // Generate report data
        $reportData = [
            'users' => $users,
            'summary' => [
                'total' => $users->count(),
                'by_status' => $users->groupBy('status')->map->count(),
                'by_location' => $users->groupBy('location')->map->count(),
                'by_company' => $users->groupBy('company.name')->map->count(),
            ]
        ];

        if ($request->expectsJson()) {
            return response()->json($reportData);
        }

        return view('reports.users', $reportData);
    }

    /**
     * Generate activity reports
     */
    public function activity(Request $request)
    {
        $query = AuditLog::with(['user', 'performedBy']);

        // Apply date filters
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $activities = $query->orderBy('created_at', 'desc')->get();

        $reportData = [
            'activities' => $activities,
            'summary' => [
                'total' => $activities->count(),
                'by_action' => $activities->groupBy('action')->map->count(),
                'by_date' => $activities->groupBy(function ($item) {
                    return $item->created_at->format('Y-m-d');
                })->map->count(),
            ]
        ];

        if ($request->expectsJson()) {
            return response()->json($reportData);
        }

        return view('reports.activity', $reportData);
    }

    /**
     * Generate provisioning reports
     */
    public function provisioning(Request $request)
    {
        // Get provisioning statistics
        $stats = [
            'total_provisioned' => User::where('status', 'active')->count(),
            'pending_provisioning' => User::where('status', 'pending')->count(),
            'failed_provisioning' => User::where('status', 'inactive')->count(),
        ];

        // Get module usage statistics
        $moduleStats = DB::table('user_modules')
            ->join('modules', 'user_modules.module_id', '=', 'modules.id')
            ->join('users', 'user_modules.user_id', '=', 'users.id')
            ->select('modules.name', 'modules.code', DB::raw('count(*) as user_count'))
            ->groupBy('modules.id', 'modules.name', 'modules.code')
            ->get();

        $reportData = [
            'stats' => $stats,
            'module_stats' => $moduleStats,
            'recent_provisions' => User::with(['company', 'modules'])
                ->whereIn('status', ['active', 'pending'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        if ($request->expectsJson()) {
            return response()->json($reportData);
        }

        return view('reports.provisioning', $reportData);
    }
}