<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceTimeLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'user_id',
        'workspace_task_id',
        'log_date',
        'started_at',
        'ended_at',
        'duration_minutes',
        'work_summary',
        'work_details',
        'status',
        'reviewed_by_user_id',
        'reviewed_at',
        'manager_notes',
        'client_visible_summary',
        'visibility',
    ];

    protected $casts = [
        // Integer FKs — cast explicitly to avoid PDO string/int mismatch in comparisons
        'workspace_id'        => 'integer',
        'user_id'             => 'integer',
        'workspace_task_id'   => 'integer',
        'reviewed_by_user_id' => 'integer',
        'duration_minutes'    => 'integer',
        // Dates / datetimes
        'log_date'    => 'date',
        'started_at'  => 'datetime',
        'ended_at'    => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // ── Labels ─────────────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'draft'     => 'Draft',
            'submitted' => 'Submitted',
            'reviewed'  => 'Reviewed',
            'approved'  => 'Approved',
            'rejected'  => 'Rejected',
        ];
    }

    public static function visibilityLabels(): array
    {
        return [
            'internal'       => 'Internal',
            'client_summary' => 'Client Summary',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function visibilityLabel(): string
    {
        return static::visibilityLabels()[$this->visibility] ?? ucfirst($this->visibility);
    }

    // ── Computed helpers ───────────────────────────────────────────────────

    /**
     * Returns duration_minutes if set, otherwise attempts to derive it from
     * started_at and ended_at. Returns null if neither is possible.
     */
    public function resolvedDurationMinutes(): ?int
    {
        if ($this->duration_minutes !== null) {
            return $this->duration_minutes;
        }

        if ($this->started_at && $this->ended_at) {
            return (int) $this->started_at->diffInMinutes($this->ended_at);
        }

        return null;
    }

    /**
     * Returns a human-readable duration string (e.g. "2h 30m").
     */
    public function durationForHumans(): string
    {
        $minutes = $this->resolvedDurationMinutes();
        if ($minutes === null) {
            return '—';
        }

        $hours   = intdiv($minutes, 60);
        $mins    = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        }
        if ($hours > 0) {
            return "{$hours}h";
        }
        return "{$mins}m";
    }

    /**
     * Returns true if this log is visible to client-role users.
     */
    public function isClientVisible(): bool
    {
        return $this->status === 'approved'
            && $this->visibility === 'client_summary';
    }

    // ── Access helpers (mirrors Workspace::resolveUserWorkspaceRole tiers) ──

    /**
     * Roles that can log time (create/edit own logs).
     */
    public static function canCreate(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager', 'talent', 'assigned_user'], true);
    }

    /**
     * Roles that can review / approve / reject time logs.
     */
    public static function canReview(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
    }

    /**
     * Roles that can view all internal time logs (including draft/submitted).
     */
    public static function canViewAll(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
    }

    /**
     * Returns true for client-side roles that see only approved client_summary entries.
     */
    public static function isClientRole(string $role): bool
    {
        return in_array($role, ['client_admin', 'client_staff', 'client'], true);
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkspaceTask::class, 'workspace_task_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }
}
