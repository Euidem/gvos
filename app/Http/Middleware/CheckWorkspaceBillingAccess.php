<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Phase 18: Checks whether a client-role user may access workspace features
 * given the workspace's current billing restriction state.
 *
 * Rules:
 *  - Admin / manager / talent roles: always pass through (billing does not block them).
 *  - Client roles on a RESTRICTED workspace: only billing, invoice, and payment routes
 *    are allowed. All other workspace routes redirect to the restricted page.
 *  - Client roles on a SUSPENDED workspace: same as restricted — billing routes pass,
 *    everything else redirects to restricted page.
 *
 * Routes that are ALWAYS accessible (even when restricted/suspended):
 *  - workspace.show
 *  - workspace.billing.*
 *  - workspace.index
 *
 * This middleware must be applied AFTER 'auth' and 'check.status'.
 */
class CheckWorkspaceBillingAccess
{
    /**
     * Route name prefixes that are always accessible even when restricted/suspended.
     */
    private const ALWAYS_ALLOWED_PREFIXES = [
        'workspace.billing.',
        'workspace.index',
        'workspace.show',
    ];

    /**
     * Exact route names that are always accessible.
     */
    private const ALWAYS_ALLOWED_EXACT = [
        'workspace.index',
        'workspace.show',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Extract the workspace from the route. Some workspace routes bind the
        // workspace model directly; we need to handle both bound and unbound cases.
        $workspace = $request->route('workspace');

        if (! ($workspace instanceof Workspace)) {
            // If it's a numeric ID or code, try to resolve
            if ($workspace) {
                $workspace = Workspace::find($workspace);
            }
        }

        if (! ($workspace instanceof Workspace)) {
            return $next($request);
        }

        // Determine workspace role for this user
        $role = $workspace->resolveUserWorkspaceRole($user);

        // Internal / operations roles are never blocked by billing state
        if (in_array($role, ['admin', 'workspace_admin', 'manager', 'talent', 'assigned_user'], true)) {
            return $next($request);
        }

        // For client roles, check billing restriction
        if (! in_array($role, ['client_admin', 'client_staff', 'client', 'observer'], true)) {
            return $next($request);
        }

        // Check if the workspace has a billing restriction
        $subscription = $workspace->activeSubscription;
        if (! $subscription) {
            return $next($request);
        }

        $isRestricted = $subscription->isRestricted() || $subscription->isSuspended();

        if (! $isRestricted) {
            return $next($request);
        }

        // Workspace is restricted/suspended. Check if the current route is allowed.
        $routeName = $request->route()?->getName() ?? '';

        foreach (self::ALWAYS_ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return $next($request);
            }
        }

        if (in_array($routeName, self::ALWAYS_ALLOWED_EXACT, true)) {
            return $next($request);
        }

        // Route is blocked — redirect to restricted page
        return redirect()->route('workspace.billing.restricted', $workspace)
            ->with('billing_blocked', true);
    }
}
