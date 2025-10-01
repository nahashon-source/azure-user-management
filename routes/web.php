<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AzureAuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\CompanyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them
| will be assigned to the "web" middleware group.
|
*/

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

// Root redirect
Route::get('/', function () {
    return redirect()->route('dashboard.index');
})->name('home');

// ============================================================================
// DASHBOARD ROUTES
// ============================================================================

Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
    Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
    Route::get('/activity', [DashboardController::class, 'getRecentActivity'])->name('activity');
});

// ============================================================================
// USER MANAGEMENT ROUTES
// ============================================================================

Route::prefix('users')->name('users.')->group(function () {
    // List and create routes
    Route::get('/', [UserManagementController::class, 'index'])->name('index');
    Route::get('/create', [UserManagementController::class, 'create'])->name('create');
    Route::post('/', [UserManagementController::class, 'store'])->name('store');
    
    // Stats endpoint (must come before {user} parameter)
    Route::get('/stats', [UserManagementController::class, 'stats'])->name('stats');
    
    // User-specific action routes (MUST come before generic {user} routes)
    Route::post('/{user}/disable', [UserManagementController::class, 'disable'])
        ->name('disable')
        ->whereNumber('user');
    
    Route::post('/{user}/enable', [UserManagementController::class, 'enable'])
        ->name('enable')
        ->whereNumber('user');
    
    Route::post('/{user}/retry-modules', [UserManagementController::class, 'retryModuleAssignments'])
        ->name('retry-modules')
        ->whereNumber('user');
    
    // Generic CRUD routes (come after specific actions)
    Route::get('/{user}', [UserManagementController::class, 'show'])
        ->name('show')
        ->whereNumber('user');
    
    Route::get('/{user}/edit', [UserManagementController::class, 'edit'])
        ->name('edit')
        ->whereNumber('user');
    
    Route::put('/{user}', [UserManagementController::class, 'update'])
        ->name('update')
        ->whereNumber('user');
    
    Route::patch('/{user}', [UserManagementController::class, 'update'])
        ->name('patch')
        ->whereNumber('user');
    
    Route::delete('/{user}', [UserManagementController::class, 'destroy'])
        ->name('destroy')
        ->whereNumber('user');
});

// ============================================================================
// MODULE MANAGEMENT ROUTES
// ============================================================================

Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', function() { 
        return view('modules.index'); 
    })->name('index');
    
    Route::get('/assign', function() { 
        return view('modules.assign'); 
    })->name('assign');
    
    // Add more module routes as needed
    // Route::get('/create', [ModuleController::class, 'create'])->name('create');
    // Route::post('/', [ModuleController::class, 'store'])->name('store');
});

// ============================================================================
// REPORTS ROUTES
// ============================================================================

Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/users', [ReportController::class, 'users'])->name('users');
    Route::get('/activity', [ReportController::class, 'activity'])->name('activity');
    Route::get('/provisioning', [ReportController::class, 'provisioning'])->name('provisioning');
    Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
});

// ============================================================================
// AZURE AUTHENTICATION ROUTES
// ============================================================================

Route::prefix('azure')->name('azure.')->group(function () {
    Route::get('/auth', [AzureAuthController::class, 'redirectToProvider'])->name('auth');
    Route::get('/callback', [AzureAuthController::class, 'handleProviderCallback'])->name('callback');
    Route::post('/test-connection', [AzureAuthController::class, 'testConnection'])->name('test');
    Route::post('/disconnect', [AzureAuthController::class, 'disconnect'])->name('disconnect');
});

// ============================================================================
// API ROUTES (AJAX/JSON endpoints)
// ============================================================================

Route::prefix('api')->name('api.')->group(function () {
    
    // Company API
    Route::get('/companies/{location}', [CompanyController::class, 'getByLocation'])
        ->name('companies.by-location');
    
    // User Management API (for AJAX operations)
    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/{user}/disable', [UserManagementController::class, 'disable'])
            ->name('disable')
            ->whereNumber('user');
        
        Route::post('/{user}/enable', [UserManagementController::class, 'enable'])
            ->name('enable')
            ->whereNumber('user');
        
        Route::post('/{user}/retry-modules', [UserManagementController::class, 'retryModuleAssignments'])
            ->name('retry-modules')
            ->whereNumber('user');
        
        Route::get('/{user}/status', [UserManagementController::class, 'getStatus'])
            ->name('status')
            ->whereNumber('user');
    });
    
    // Dashboard API
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats'])->name('stats');
        Route::get('/activity', [DashboardController::class, 'getRecentActivity'])->name('activity');
    });
});

// ============================================================================
// PROFILE ROUTES (if using Breeze/Jetstream)
// ============================================================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ============================================================================
// AUTHENTICATION ROUTES (if needed)
// ============================================================================

// Uncomment if you're using Laravel Breeze or similar
// require __DIR__.'/auth.php';

// ============================================================================
// HEALTH CHECK & UTILITY ROUTES
// ============================================================================

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
    ]);
})->name('health');

// ============================================================================
// ERROR HANDLING ROUTES
// ============================================================================

// 404 Handler - Custom error page
Route::fallback(function () {
    if (request()->expectsJson()) {
        return response()->json([
            'message' => 'Route not found',
            'status' => 404
        ], 404);
    }
    
    return redirect()->route('dashboard.index')
        ->with('warning', 'The page you were looking for was not found.');
});