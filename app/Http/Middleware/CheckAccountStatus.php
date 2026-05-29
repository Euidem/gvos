<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountStatus
{
    /**
     * Block suspended and inactive users from accessing role dashboards.
     * Redirects them to the account status page.
     * Pending users are allowed through — they see their dashboard notice.
     * Admin users (Filament) are handled separately by Filament's gate.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isAccessBlocked()) {
            // Always allow the logout POST through
            if ($request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('account.status');
        }

        return $next($request);
    }
}
