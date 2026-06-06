<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workspace extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_code',
        'lead_request_id',
        'trial_id',
        'company_id',
        'client_profile_id',
        'primary_manager_id',
        'primary_talent_id',
        'name',
        'description',
        'status',
        'type',
        'starts_at',
        'ends_at',
        'task_limit',
        'file_limit_mb',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    // ── Status labels ─────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'pending'   => 'Pending',
            'active'    => 'Active',
            'paused'    => 'Paused',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function typeLabels(): array
    {
        return [
            'trial'    => 'Trial',
            'ongoing'  => 'Ongoing',
            'project'  => 'Project',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public static function generateCode(): string
    {
        $latest = static::withTrashed()->max('id') ?? 0;
        return 'WS-' . str_pad($latest + 1, 6, '0', STR_PAD_LEFT);
    }

    // ── Access helpers ────────────────────────────────────────────────────

    /**
     * Resolve the effective role this user holds in the workspace.
     *
     * Priority / tier order:
     *   admin          – super_admin or operations_admin system role
     *   workspace_admin– active member with role=workspace_admin (highest workspace power)
     *   manager        – primary_manager_id match OR active member with role=manager
     *   talent         – primary_talent_id match OR active member with role=talent
     *   client_admin   – active member with role=client_admin (or legacy role=client)
     *   client_staff   – active member with role=client_staff
     *   observer       – active member with role=observer
     *   assigned_user  – assigned to at least one task in this workspace (no member row)
     *   none           – no access
     *
     * Note: primary_manager_id / primary_talent_id are stored without model casts.
     * Both sides are explicitly cast to int to avoid Eloquent string/integer mismatch.
     */
    public function resolveUserWorkspaceRole(User $user): string
    {
        // Tier 1 – system admins always get full admin access
        if ($user->hasAnyRole(['super_admin', 'operations_admin'])) {
            return 'admin';
        }

        // Load active member row once for this user (avoids repeated queries below)
        $member = $this->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // Tier 2 – workspace_admin member (designated workspace-level administrator)
        if ($member && $member->role === 'workspace_admin') {
            return 'workspace_admin';
        }

        // Tier 3 – primary manager (int cast to avoid Eloquent string/int mismatch)
        if ($this->primary_manager_id !== null
            && (int) $this->primary_manager_id === (int) $user->id) {
            return 'manager';
        }

        // Tier 4 – active member with manager role
        if ($member && $member->role === 'manager') {
            return 'manager';
        }

        // Tier 5 – primary talent
        if ($this->primary_talent_id !== null
            && (int) $this->primary_talent_id === (int) $user->id) {
            return 'talent';
        }

        // Tier 6 – active member with talent / client / observer roles
        if ($member) {
            return match ($member->role) {
                'talent'       => 'talent',
                'client_admin' => 'client_admin',
                'client_staff' => 'client_staff',
                'client'       => 'client_admin', // legacy: treat as client_admin
                'observer'     => 'observer',
                default        => 'observer',
            };
        }

        // Tier 7 – assigned to a task in this workspace (no member row required)
        if ($this->tasks()->where('assigned_to_user_id', $user->id)->exists()) {
            return 'assigned_user';
        }

        return 'none';
    }

    /**
     * Returns true if the user may view this workspace and its task board.
     */
    public function userHasAccess(User $user): bool
    {
        return $this->resolveUserWorkspaceRole($user) !== 'none';
    }

    /**
     * Returns true if the user may create tasks in this workspace.
     * Admins, workspace_admins, managers, and talent can create tasks.
     */
    public function userCanCreateTasks(User $user): bool
    {
        return in_array($this->resolveUserWorkspaceRole($user),
            ['admin', 'workspace_admin', 'manager', 'talent', 'assigned_user'], true);
    }

    /**
     * Returns true if the user may manage (edit/delete/assign) tasks in this workspace.
     * Admins, workspace_admins, and managers can manage tasks.
     */
    public function userCanManageTasks(User $user): bool
    {
        return in_array($this->resolveUserWorkspaceRole($user),
            ['admin', 'workspace_admin', 'manager'], true);
    }

    /**
     * Returns true if the user may view internal notes and internal comments.
     * Only admins, workspace_admins, and managers see internal content.
     */
    public function userCanViewInternalTaskNotes(User $user): bool
    {
        return in_array($this->resolveUserWorkspaceRole($user),
            ['admin', 'workspace_admin', 'manager'], true);
    }

    // ── Primary-team sync ─────────────────────────────────────────────────

    /**
     * Ensure the primary manager and primary talent each have an active
     * WorkspaceMember row in this workspace.
     *
     * Rules:
     *   – If no row exists → create with status=active, role=manager|talent.
     *   – If a row exists but is removed, or has the wrong role → reactivate
     *     and correct the role.
     *   – If already active with the correct role → skip (no-op).
     *
     * Returns a summary array keyed by 'added', 'reactivated', 'skipped'.
     * Each value is an array of ['user_id' => …, 'role' => …] entries.
     *
     * @return array{added: array, reactivated: array, skipped: array}
     */
    public function syncPrimaryTeamToMembers(): array
    {
        $result = ['added' => [], 'reactivated' => [], 'skipped' => []];

        $candidates = [];
        if ($this->primary_manager_id) {
            $candidates[(int) $this->primary_manager_id] = 'manager';
        }
        if ($this->primary_talent_id) {
            $candidates[(int) $this->primary_talent_id] = 'talent';
        }

        foreach ($candidates as $userId => $role) {
            $member = $this->members()->where('user_id', $userId)->first();

            if (! $member) {
                // No member row at all — create one.
                WorkspaceMember::create([
                    'workspace_id' => $this->id,
                    'user_id'      => $userId,
                    'role'         => $role,
                    'status'       => 'active',
                    'joined_at'    => now(),
                ]);
                $result['added'][] = ['user_id' => $userId, 'role' => $role];
            } elseif ($member->status === 'removed' || $member->role !== $role) {
                // Existing row needs reactivation or role correction.
                $member->update([
                    'role'      => $role,
                    'status'    => 'active',
                    'joined_at' => $member->joined_at ?? now(),
                ]);
                $result['reactivated'][] = ['user_id' => $userId, 'role' => $role];
            } else {
                // Already an active member with the correct role — nothing to do.
                $result['skipped'][] = ['user_id' => $userId, 'role' => $role];
            }
        }

        return $result;
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function leadRequest(): BelongsTo
    {
        return $this->belongsTo(LeadRequest::class);
    }

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function primaryManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_manager_id');
    }

    public function primaryTalent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_talent_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class)->where('status', 'active');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(WorkspaceTask::class)->orderBy('sort_order')->orderBy('created_at');
    }

    public function openTasks(): HasMany
    {
        return $this->hasMany(WorkspaceTask::class)
            ->whereIn('status', ['pending', 'in_progress', 'blocked', 'submitted', 'revision_requested']);
    }

    // ── Phase 6 relationships ─────────────────────────────────────────────

    public function messages(): HasMany
    {
        return $this->hasMany(WorkspaceMessage::class)->orderBy('created_at');
    }

    public function files(): HasMany
    {
        return $this->hasMany(WorkspaceFile::class)->orderByDesc('created_at');
    }

    // ── Phase 7 relationships ─────────────────────────────────────────────

    public function timeLogs(): HasMany
    {
        return $this->hasMany(WorkspaceTimeLog::class)->orderByDesc('log_date')->orderByDesc('created_at');
    }

    public function weeklyReports(): HasMany
    {
        return $this->hasMany(WorkspaceWeeklyReport::class)->orderByDesc('week_start_date');
    }

    // ── Phase 8 — Billing relationships ──────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(WorkspaceSubscription::class)->orderByDesc('created_at');
    }

    public function activeSubscription(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(WorkspaceSubscription::class)
            ->whereIn('status', ['trial', 'active', 'payment_due', 'overdue'])
            ->latestOfMany();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->orderByDesc('issue_date');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('created_at');
    }
}
