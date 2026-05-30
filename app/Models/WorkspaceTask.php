<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'task_code',
        'workspace_id',
        'created_by_user_id',
        'assigned_to_user_id',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'started_at',
        'submitted_at',
        'approved_at',
        'closed_at',
        'sort_order',
        'internal_notes',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'started_at'   => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'closed_at'    => 'datetime',
    ];

    // ── Labels ────────────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'pending'            => 'Pending',
            'in_progress'        => 'In Progress',
            'blocked'            => 'Blocked',
            'submitted'          => 'Submitted',
            'revision_requested' => 'Revision Requested',
            'approved'           => 'Approved',
            'closed'             => 'Closed',
            'cancelled'          => 'Cancelled',
        ];
    }

    public static function priorityLabels(): array
    {
        return [
            'low'    => 'Low',
            'normal' => 'Normal',
            'high'   => 'High',
            'urgent' => 'Urgent',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function priorityLabel(): string
    {
        return static::priorityLabels()[$this->priority] ?? ucfirst($this->priority);
    }

    // ── Status flow ───────────────────────────────────────────────────────

    /**
     * Returns the allowed next statuses for a given current status and role.
     *
     * Roles: 'admin', 'manager', 'talent', 'client', 'observer'
     */
    public static function allowedTransitions(string $fromStatus, string $role): array
    {
        if (in_array($role, ['admin', 'manager'])) {
            return match ($fromStatus) {
                'pending'            => ['in_progress', 'cancelled'],
                'in_progress'        => ['blocked', 'submitted', 'pending', 'cancelled'],
                'blocked'            => ['in_progress', 'cancelled'],
                'submitted'          => ['revision_requested', 'approved', 'in_progress'],
                'revision_requested' => ['in_progress', 'cancelled'],
                'approved'           => ['closed'],
                'closed'             => [],
                'cancelled'          => [],
                default              => [],
            };
        }

        if ($role === 'talent') {
            return match ($fromStatus) {
                'pending'            => ['in_progress'],
                'in_progress'        => ['blocked', 'submitted'],
                'blocked'            => ['in_progress'],
                'revision_requested' => ['in_progress'],
                default              => [],
            };
        }

        if ($role === 'client') {
            return match ($fromStatus) {
                'submitted' => ['revision_requested', 'approved'],
                'approved'  => ['closed'],
                default     => [],
            };
        }

        return [];
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public static function generateCode(): string
    {
        $latest = static::withTrashed()->max('id') ?? 0;
        return 'TASK-' . str_pad($latest + 1, 5, '0', STR_PAD_LEFT);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['pending', 'in_progress', 'blocked', 'submitted', 'revision_requested']);
    }

    public function isDueSoon(): bool
    {
        return $this->due_date
            && $this->isOpen()
            && $this->due_date->lte(now()->addDays(3));
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->isOpen()
            && $this->due_date->lt(now()->startOfDay());
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(WorkspaceTaskComment::class)->orderBy('created_at');
    }
}
