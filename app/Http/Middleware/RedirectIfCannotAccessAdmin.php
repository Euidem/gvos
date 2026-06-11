<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Friendly guard for the GVOS admin (Filament) panel.
 *
 * If an authenticated non-admin reaches an /admin route — whether by a stale
 * intended URL or by typing it manually — send them to their workspace
 * dashboard with a notice instead of an ugly 403.
 *
 * This does NOT weaken Filament security: guests still fall through to
 * Filament's Authenticate gate, and User::canAccessPanel() remains the
 * authoritative panel gate. We only intercept the authenticated-but-unauthorised
 * case to redirect away (still blocking access) rather than 403.
 */
class RedirectIfCannotAccessAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->canAccessAdminPanel()) {
            return redirect($user->getDashboardRoute())
                ->with('status', 'You do not have access to the admin area. You have been redirected to your workspace dashboard.');
        }

        return $next($request);
    }
}
