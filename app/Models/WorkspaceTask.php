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
        // Integer FKs — cast explicitly to avoid PHP PDO returning them as strings,
        // which would break strict === comparisons used in access checks.
        'workspace_id'        => 'integer',
        'created_by_user_id'  => 'integer',
        'assigned_to_user_id' => 'integer',
        'sort_order'          => 'integer',
        // Dates / datetimes
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
     * Roles accepted: 'admin' | 'manager' | 'talent' | 'client' | 'observer'
     *
     * Note: 'assigned_user' is mapped to 'talent' before this method is called
     * via WorkspaceTaskController::transitionRole().
     *
     * Admin / Manager:  broad operational control — can move tasks in any
     *                   operationally-sensible direction, plus cancel anything
     *                   that is still in-flight.
     *
     * Talent:           can self-advance work they are doing and submit for
     *                   review. Can only update tasks assigned to themselves
     *                   (enforced in WorkspaceTaskController::updateStatus).
     *
     * Client:           review-only — can approve or request revision on
     *                   submitted work, and close approved work.
     *
     * Observer / none:  read-only — no transitions allowed.
     */
    public static function allowedTransitions(string $fromStatus, string $role): array
    {
        // ── Admin / Manager ───────────────────────────────────────────────
        if (in_array($role, ['admin', 'manager'], true)) {
            return match ($fromStatus) {
                // Start work or cancel
                'pending'            => ['in_progress', 'cancelled'],
                // Advance, block, revert to pending, submit, or cancel
                'in_progress'        => ['blocked', 'submitted', 'pending', 'cancelled'],
                // Unblock or cancel
                'blocked'            => ['in_progress', 'cancelled'],
                // Review outcome: approve, request revision, or push back to in-progress
                'submitted'          => ['approved', 'revision_requested', 'in_progress'],
                // Send back to work or cancel
                'revision_requested' => ['in_progress', 'cancelled'],
                // Close approved work
                'approved'           => ['closed'],
                // Terminal states — nothing further
                'closed'             => [],
                'cancelled'          => [],
                default              => [],
            };
        }

        // ── Talent ────────────────────────────────────────────────────────
        // Can self-advance their own assigned work. Cannot approve or close.
        if ($role === 'talent') {
            return match ($fromStatus) {
                'pending'            => ['in_progress'],
                'in_progress'        => ['blocked', 'submitted'],
                'blocked'            => ['in_progress'],
                'revision_requested' => ['in_progress'],
                default              => [],
            };
        }

        // ── Client ────────────────────────────────────────────────────────
        // Review-only: can approve submitted work, request revision, or close approved.
        if ($role === 'client') {
            return match ($fromStatus) {
                'submitted' => ['approved', 'revision_requested'],
                'approved'  => ['closed'],
                default     => [],
            };
        }

        // ── Observer / unrecognised role ──────────────────────────────────
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
