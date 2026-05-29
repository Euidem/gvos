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

    // ── Phase 2 — People & Organizations ─────────────────────────────────

    public static function companyCreated(Model $company, array $extra = []): void
    {
        self::log('company.created', $company, array_merge(['name' => $company->name ?? null], $extra));
    }

    public static function companyUpdated(Model $company, array $changes = []): void
    {
        self::log('company.updated', $company, $changes);
    }

    public static function departmentCreated(Model $department, array $extra = []): void
    {
        self::log('department.created', $department, array_merge(['name' => $department->name ?? null], $extra));
    }

    public static function departmentUpdated(Model $department, array $changes = []): void
    {
        self::log('department.updated', $department, $changes);
    }

    public static function clientProfileCreated(Model $clientProfile, array $extra = []): void
    {
        self::log('client_profile.created', $clientProfile, array_merge(['user_id' => $clientProfile->user_id ?? null], $extra));
    }

    public static function clientProfileUpdated(Model $clientProfile, array $changes = []): void
    {
        self::log('client_profile.updated', $clientProfile, $changes);
    }

    public static function talentProfileCreated(Model $talentProfile, array $extra = []): void
    {
        self::log('talent_profile.created', $talentProfile, array_merge(['user_id' => $talentProfile->user_id ?? null], $extra));
    }

    public static function talentProfileUpdated(Model $talentProfile, array $changes = []): void
    {
        self::log('talent_profile.updated', $talentProfile, $changes);
    }

    public static function managerProfileCreated(Model $managerProfile, array $extra = []): void
    {
        self::log('manager_profile.created', $managerProfile, array_merge(['user_id' => $managerProfile->user_id ?? null], $extra));
    }

    public static function managerProfileUpdated(Model $managerProfile, array $changes = []): void
    {
        self::log('manager_profile.updated', $managerProfile, $changes);
    }
}
