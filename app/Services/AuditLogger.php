<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an auditable action.
     *
     * @param  string       $action   Dot-namespaced action: user.created, user.role_changed …
     * @param  Model|null   $subject  The model that was acted upon.
     * @param  array        $context  Extra key→value pairs (from/to values, metadata).
     * @param  int|null     $actorId  Override the actor; defaults to the authenticated user.
     */
    public static function log(
        string  $action,
        ?Model  $subject = null,
        array   $context = [],
        ?int    $actorId = null,
    ): void {
        try {
            /** @var Request $request */
            $request = app('request');

            AuditLog::create([
                'user_id'      => $actorId ?? Auth::id(),
                'action'       => $action,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->getKey(),
                'context'      => array_merge(
                    ['_actor_name' => Auth::user()?->name ?? 'system'],
                    $context,
                ),
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let audit logging break the main request.
            // In production, consider logging to the error log here.
            \Illuminate\Support\Facades\Log::warning(
                'AuditLogger::log failed: ' . $e->getMessage(),
                ['action' => $action]
            );
        }
    }

    // ── Convenience wrappers ─────────────────────────────────────────────

    public static function userCreated(Model $user, array $extra = []): void
    {
        self::log('user.created', $user, array_merge(['email' => $user->email ?? null], $extra));
    }

    public static function userUpdated(Model $user, array $changes = []): void
    {
        self::log('user.updated', $user, $changes);
    }

    public static function roleChanged(Model $user, string $from, string $to): void
    {
        self::log('user.role_changed', $user, ['from' => $from, 'to' => $to]);
    }

    public static function statusChanged(Model $user, string $from, string $to): void
    {
        self::log('user.status_changed', $user, ['from' => $from, 'to' => $to]);
    }

    public static function passwordChanged(Model $user): void
    {
        self::log('user.password_changed', $user);
    }

    public static function profileUpdated(Model $user, array $changes = []): void
    {
        self::log('user.profile_updated', $user, $changes);
    }

    public static function login(Model $user): void
    {
        self::log('user.login', $user, ['email' => $user->email ?? null]);
    }
}
