<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        // ── Route middleware aliases ───────────────────────────────────────
        //
        // IMPORTANT: In Laravel 11, app/Http/Kernel.php no longer exists.
        // All middleware aliases must be registered here.
        //
        // Spatie Laravel Permission v6 middleware aliases are NOT auto-registered
        // in Laravel 11 — they must be declared explicitly.
        //
        $middleware->alias([
            // Spatie Permission — role and permission guards
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            // GVOS — account status gate (blocks suspended / inactive users)
            'check.status'       => \App\Http\Middleware\CheckAccountStatus::class,

            // GVOS Phase 18 — billing access gate (blocks restricted/suspended workspace access for clients)
            'check.billing'      => \App\Http\Middleware\CheckWorkspaceBillingAccess::class,
        ]);

        // Inertia middleware appended to web group.
        // Currently a pass-through (Blade-only in Phase 0/1).
        // Will inject shared props when React pages are introduced in Phase 2+.
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
