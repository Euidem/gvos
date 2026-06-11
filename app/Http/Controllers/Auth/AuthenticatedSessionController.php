<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): View
    {
        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'status'           => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     * On success, redirect to the role-appropriate dashboard.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        AuditLogger::login($user);

        $fallback = $user->getDashboardRoute();

        // Honour a previously intended URL — but never trap a non-admin on the
        // Filament admin panel. A guest who hits /admin (or is bounced to login
        // from it) has /admin stored as the intended URL; honouring that here is
        // exactly what produced the post-login 403 for non-admins. Discard it.
        $intended = $request->session()->pull('url.intended');

        if ($intended && ! $this->intendedTargetsAdminPanel($intended)) {
            return redirect()->to($intended);
        }

        if ($intended && $user->canAccessAdminPanel()) {
            return redirect()->to($intended);
        }

        return redirect()->to($fallback);
    }

    /**
     * True when an intended URL points at the Filament admin panel (/admin...).
     */
    protected function intendedTargetsAdminPanel(string $intended): bool
    {
        $path = '/' . ltrim((string) parse_url($intended, PHP_URL_PATH), '/');

        return $path === '/admin' || Str::startsWith($path, '/admin/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
