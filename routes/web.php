<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceTaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GVOS Web Routes
|--------------------------------------------------------------------------
|
| Middleware used:
|   auth          — user must be logged in
|   check.status  — block suspended / inactive users from dashboards
|   role:X        — user must hold the specified Spatie role(s)
|
| Filament handles all /admin routes separately via AdminPanelProvider.
|
*/

// ── Root redirect ────────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->getDashboardRoute());
    }
    return redirect()->route('login');
});

// ── Account status page (suspended / inactive users land here) ───────────
Route::middleware('auth')->group(function () {
    Route::get('/account/status', function () {
        return view('account.status');
    })->name('account.status');
});

// ── Profile routes (all authenticated users) ─────────────────────────────
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/profile',  [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');
});

// ── Workspace routes (all authenticated, active users) ────────────────────
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/workspaces',            [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('workspace.show');

    // ── Workspace task routes (Phase 5) ───────────────────────────────────
    Route::prefix('workspaces/{workspace}/tasks')->name('workspace.tasks.')->group(function () {
        Route::get('/',                          [WorkspaceTaskController::class, 'index'])->name('index');
        Route::get('/create',                    [WorkspaceTaskController::class, 'create'])->name('create');
        Route::post('/',                         [WorkspaceTaskController::class, 'store'])->name('store');
        Route::get('/{task}',                    [WorkspaceTaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit',               [WorkspaceTaskController::class, 'edit'])->name('edit');
        Route::put('/{task}',                    [WorkspaceTaskController::class, 'update'])->name('update');
        Route::post('/{task}/comments',          [WorkspaceTaskController::class, 'storeComment'])->name('comments.store');
        Route::post('/{task}/status',            [WorkspaceTaskController::class, 'updateStatus'])->name('status.update');
    });
});

// ── Manager Console ──────────────────────────────────────────────────────
Route::middleware(['auth', 'check.status', 'role:line_manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'lineManager'])->name('dashboard');
    });

// ── Talent Portal ────────────────────────────────────────────────────────
Route::middleware(['auth', 'check.status', 'role:talent'])
    ->prefix('talent')
    ->name('talent.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'talent'])->name('dashboard');
    });

// ── Client Portal (individual + business roles) ──────────────────────────
Route::middleware([
    'auth',
    'check.status',
    'role:individual_client|business_client_admin|business_client_staff',
])
    ->prefix('client')
    ->name('client.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'client'])->name('dashboard');
    });

// ── Active Lead Portal ───────────────────────────────────────────────────
Route::middleware(['auth', 'check.status', 'role:active_lead'])
    ->prefix('lead')
    ->name('lead.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'lead'])->name('dashboard');
    });

// ── Public lead request form (no auth required) ──────────────────────────
Route::get('/request-service', [LeadRequestController::class, 'show'])->name('lead.request-service');
Route::post('/request-service', [LeadRequestController::class, 'store'])->name('lead.request-service.store');
Route::get('/request-service/success', fn () => view('lead.request-service-success'))->name('lead.request-service.success');

// ── Auth routes ──────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
