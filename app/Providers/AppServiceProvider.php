<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Phase 21: Rate limiters for sensitive portal endpoints.
        // Applied via throttle:<name> middleware on individual routes in routes/web.php.

        // Vault secret reveal — 10 requests per minute per authenticated user (or IP as fallback)
        RateLimiter::for('vault-reveal', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // File uploads — 20 requests per minute per authenticated user
        RateLimiter::for('file-upload', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        // Chat message send — 30 messages per minute per authenticated user
        RateLimiter::for('chat-send', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Invitation registration and accept — 10 requests per minute per IP
        // (Public endpoint; no authenticated user available during registration.)
        RateLimiter::for('invitation', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
