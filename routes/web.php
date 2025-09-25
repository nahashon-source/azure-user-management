<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AzureAuthController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Public routes - no authentication required
Route::get('/', function () {
    return redirect('/dashboard');
});

// Dashboard routes - remove middleware
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
Route::get('/dashboard/reports', [DashboardController::class, 'reports'])->name('dashboard.reports');

// User Management routes - remove middleware
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [UserManagementController::class, 'index'])->name('index');
    Route::get('/create', [UserManagementController::class, 'create'])->name('create');
    Route::post('/', [UserManagementController::class, 'store'])->name('store');
    Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
    Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
    Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
    Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/enable', [UserManagementController::class, 'enable'])->name('enable');
});

// Module Management routes - remove middleware
Route::prefix('modules')->name('modules.')->group(function () {
    Route::get('/', function() { 
        return view('modules.index'); 
    })->name('index');
    Route::get('/assign', function() { 
        return view('modules.assign'); 
    })->name('assign');
});

// Reports routes - remove middleware
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/users', [ReportController::class, 'users'])->name('users');
    Route::get('/activity', [ReportController::class, 'activity'])->name('activity');
    Route::get('/provisioning', [ReportController::class, 'provisioning'])->name('provisioning');
});

// Azure Authentication routes - remove middleware
Route::prefix('azure')->name('azure.')->group(function () {
    Route::get('/auth', [AzureAuthController::class, 'redirectToProvider'])->name('auth');
    Route::get('/callback', [AzureAuthController::class, 'handleProviderCallback'])->name('callback');
    Route::post('/test-connection', [AzureAuthController::class, 'testConnection'])->name('test');
});

// Fallback for undefined routes
Route::fallback(function () {
    return redirect('/dashboard');
});