<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceBillingController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceFileController;
use App\Http\Controllers\WorkspaceInvitationController;
use App\Http\Controllers\WorkspaceMemberController;
use App\Http\Controllers\WorkspaceMessageController;
use App\Http\Controllers\WorkspaceTaskController;
use App\Http\Controllers\WorkspaceTimeLogController;
use App\Http\Controllers\WorkspaceTimeTrackerController;
use App\Http\Controllers\WorkspaceVaultController;
use App\Http\Controllers\WorkspaceWeeklyReportController;
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

// ── Profile and onboarding routes (all authenticated users) ──────────────
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/profile',  [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile',  [ProfileController::class, 'update'])->name('profile.update');

    // ── Onboarding routes (Phase 16) ──────────────────────────────────────
    Route::get('/onboarding',              [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding/profile',     [OnboardingController::class, 'update'])->name('onboarding.profile.update');
    Route::post('/onboarding/complete',    [OnboardingController::class, 'completeStep'])->name('onboarding.step.complete');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('/settings/notifications', [NotificationController::class, 'settings'])->name('settings.notifications');
    Route::put('/settings/notifications', [NotificationController::class, 'updateSettings'])->name('settings.notifications.update');

    Route::post('/invitations/{token}/accept', [WorkspaceInvitationController::class, 'accept'])->name('workspace.invitations.accept');
});

// ── Workspace routes (all authenticated, active users) ────────────────────
Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/workspaces',            [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::get('/workspaces/{workspace}', [WorkspaceController::class, 'show'])->name('workspace.show');
    Route::get('/time-tracker/current',   [WorkspaceTimeTrackerController::class, 'current'])->name('time-tracker.current');

    // Workspace member and invitation routes (Phase 13)
    Route::prefix('workspaces/{workspace}/members')->name('workspace.members.')->group(function () {
        Route::get('/', [WorkspaceMemberController::class, 'index'])->name('index');
        Route::post('/', [WorkspaceMemberController::class, 'store'])->name('store');
        Route::get('/invite', [WorkspaceMemberController::class, 'invite'])->name('invite');
        Route::post('/invite', [WorkspaceMemberController::class, 'sendInvitation'])->name('invite.store');
        Route::put('/{member}', [WorkspaceMemberController::class, 'update'])->name('update');
        Route::post('/{member}/deactivate', [WorkspaceMemberController::class, 'deactivate'])->name('deactivate');
        Route::post('/invitations/{invitation}/resend', [WorkspaceMemberController::class, 'resendInvitation'])->name('invitations.resend');
        Route::post('/invitations/{invitation}/revoke', [WorkspaceMemberController::class, 'revokeInvitation'])->name('invitations.revoke');
    });

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
        Route::post('/{task}/files',             [WorkspaceFileController::class, 'storeForTask'])->name('files.store');
    });

    // ── Workspace chat routes (Phase 6) ───────────────────────────────────
    Route::prefix('workspaces/{workspace}/chat')->name('workspace.chat.')->group(function () {
        Route::get('/',              [WorkspaceMessageController::class, 'index'])->name('index');
        Route::post('/',             [WorkspaceMessageController::class, 'store'])->name('store');
        Route::delete('/{message}',  [WorkspaceMessageController::class, 'destroy'])->name('destroy');
    });

    // ── Workspace file routes (Phase 6) ───────────────────────────────────
    Route::prefix('workspaces/{workspace}/files')->name('workspace.files.')->group(function () {
        Route::get('/',                      [WorkspaceFileController::class, 'index'])->name('index');
        Route::post('/',                     [WorkspaceFileController::class, 'store'])->name('store');
        Route::get('/{file}/download',       [WorkspaceFileController::class, 'download'])->name('download');
        Route::delete('/{file}',             [WorkspaceFileController::class, 'destroy'])->name('destroy');
    });

    // ── Workspace billing routes (Phase 8) ───────────────────────────────
    Route::prefix('workspaces/{workspace}/billing')->name('workspace.billing.')->group(function () {
        Route::get('/',                        [WorkspaceBillingController::class, 'index'])->name('index');
        Route::get('/invoices/{invoice}',      [WorkspaceBillingController::class, 'showInvoice'])->name('invoice');
        Route::get('/payments',                [WorkspaceBillingController::class, 'payments'])->name('payments');
    });

    // ── Workspace password vault routes (Phase 10) ──────────────────────────
    Route::prefix('workspaces/{workspace}/vault')->name('workspace.vault.')->group(function () {
        Route::get('/',                         [WorkspaceVaultController::class, 'index'])->name('index');
        Route::get('/create',                   [WorkspaceVaultController::class, 'create'])->name('create');
        Route::post('/',                        [WorkspaceVaultController::class, 'store'])->name('store');
        Route::get('/{vaultItem}',              [WorkspaceVaultController::class, 'show'])->name('show');
        Route::get('/{vaultItem}/edit',         [WorkspaceVaultController::class, 'edit'])->name('edit');
        Route::put('/{vaultItem}',              [WorkspaceVaultController::class, 'update'])->name('update');
        Route::post('/{vaultItem}/reveal',      [WorkspaceVaultController::class, 'reveal'])->name('reveal');
        Route::post('/{vaultItem}/archive',     [WorkspaceVaultController::class, 'archive'])->name('archive');
        Route::get('/{vaultItem}/access-logs',  [WorkspaceVaultController::class, 'accessLogs'])->name('access-logs');
    });

    // ── Workspace time log routes (Phase 7) ───────────────────────────────
    Route::prefix('workspaces/{workspace}/time-logs')->name('workspace.time-logs.')->group(function () {
        Route::get('/',            [WorkspaceTimeLogController::class, 'index'])->name('index');
        Route::get('/create',      [WorkspaceTimeLogController::class, 'create'])->name('create');
        Route::post('/',           [WorkspaceTimeLogController::class, 'store'])->name('store');
        Route::get('/{timeLog}',   [WorkspaceTimeLogController::class, 'show'])->name('show');
        Route::get('/{timeLog}/edit',    [WorkspaceTimeLogController::class, 'edit'])->name('edit');
        Route::put('/{timeLog}',         [WorkspaceTimeLogController::class, 'update'])->name('update');
        Route::post('/{timeLog}/review', [WorkspaceTimeLogController::class, 'review'])->name('review');
        Route::delete('/{timeLog}',      [WorkspaceTimeLogController::class, 'destroy'])->name('destroy');
    });

    // ── Workspace time tracker routes (Phase 9) ───────────────────────────
    Route::prefix('workspaces/{workspace}/time-tracker')->name('workspace.time-tracker.')->group(function () {
        Route::post('/start',    [WorkspaceTimeTrackerController::class, 'start'])->name('start');
        Route::post('/stop',     [WorkspaceTimeTrackerController::class, 'stop'])->name('stop');
        Route::post('/complete', [WorkspaceTimeTrackerController::class, 'complete'])->name('complete');
    });

    // ── Workspace weekly report routes (Phase 7 + Phase 17) ───────────────
    Route::prefix('workspaces/{workspace}/reports')->name('workspace.reports.')->group(function () {
        Route::get('/',              [WorkspaceWeeklyReportController::class, 'index'])->name('index');
        Route::get('/create',        [WorkspaceWeeklyReportController::class, 'create'])->name('create');
        Route::post('/',             [WorkspaceWeeklyReportController::class, 'store'])->name('store');
        // Phase 17: generate route must precede /{report} to avoid route collision with slug "generate"
        Route::get('/generate',      [WorkspaceWeeklyReportController::class, 'generate'])->name('generate');
        Route::post('/generate',     [WorkspaceWeeklyReportController::class, 'generateStore'])->name('generate.store');
        Route::get('/{report}',      [WorkspaceWeeklyReportController::class, 'show'])->name('show');
        Route::get('/{report}/edit', [WorkspaceWeeklyReportController::class, 'edit'])->name('edit');
        Route::put('/{report}',          [WorkspaceWeeklyReportController::class, 'update'])->name('update');
        Route::delete('/{report}',       [WorkspaceWeeklyReportController::class, 'destroy'])->name('destroy');
        // Phase 17: dedicated publish action
        Route::post('/{report}/publish', [WorkspaceWeeklyReportController::class, 'publish'])->name('publish');
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

// ── Invitation routes (Phase 14) ─────────────────────────────────────────
Route::get('/invitations/{token}', [WorkspaceInvitationController::class, 'show'])->name('workspace.invitations.show');
Route::post('/invitations/{token}/register', [WorkspaceInvitationController::class, 'registerAndAccept'])->name('workspace.invitations.register');

// ── Auth routes ──────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';
