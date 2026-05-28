<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    /**
     * Show the email verification prompt.
     * Invokable — used as EmailVerificationPromptController::class in routes.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->intended(auth()->user()->getDashboardRoute())
            : view('auth.verify-email', ['status' => session('status')]);
    }
}
