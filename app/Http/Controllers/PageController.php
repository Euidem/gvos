<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Lightweight page/redirect actions for routes that previously used closures.
 *
 * Closure route actions cannot be serialized by `php artisan route:cache`, which
 * breaks production deployment. Moving these into a controller makes the full
 * route table cacheable. No business logic changed — behaviour is identical.
 */
class PageController extends Controller
{
    /**
     * Root redirect: authenticated users go to their role dashboard, guests to login.
     */
    public function home(Request $request): RedirectResponse
    {
        if ($request->user()) {
            return redirect($request->user()->getDashboardRoute());
        }

        return redirect()->route('login');
    }

    /**
     * Account status holding page (suspended / inactive users land here).
     */
    public function accountStatus(): View
    {
        return view('account.status');
    }
}
