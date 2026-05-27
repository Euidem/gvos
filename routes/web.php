<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| GVOS Web Routes
|--------------------------------------------------------------------------
|
| All portal routes. Filament handles /admin routes separately.
| Routes are guarded by Spatie role middleware.
|
| Middleware used:
|   auth       — user must be logged in
|   role:X     — user must have the specified role(s)
|   verified   — email must be verified (Phase 1+)
|
*/

// Root: redirect authenticated users to their role dashboard
Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->getDashboardRoute());
    }
    return redirect()->route('login');
});

// -----------------------------------------------------------------------
// Manager Console Routes
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:line_manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'lineManager'])->name('dashboard');
});

// -----------------------------------------------------------------------
// Talent Portal Routes
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:talent'])->prefix('talent')->name('talent.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'talent'])->name('dashboard');
});

// -----------------------------------------------------------------------
// Client Portal Routes
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:individual_client|business_client_admin|business_client_staff'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard');
    });

// -----------------------------------------------------------------------
// Active Lead Routes
// -----------------------------------------------------------------------
Route::middleware(['auth', 'role:active_lead'])->prefix('lead')->name('lead.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'lead'])->name('dashboard');
});

// -----------------------------------------------------------------------
// Auth Routes (provided by Laravel Breeze)
// -----------------------------------------------------------------------
require __DIR__ . '/auth.php';
