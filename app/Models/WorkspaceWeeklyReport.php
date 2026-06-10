<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceWeeklyReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'week_start_date',
        'week_end_date',
        'prepared_by_user_id',
        'reviewed_by_user_id',
        'total_minutes',
        'summary',
        'achievements',
        'blockers',
        'next_steps',
        'client_notes',
        'status',
        'published_at',
        // Phase 17 — generation metadata
        'generated_at',
        'generated_by_user_id',
    ];

    protected $casts = [
        // Integer FKs
        'workspace_id'          => 'integer',
        'prepared_by_user_id'   => 'integer',
        'reviewed_by_user_id'   => 'integer',
        'generated_by_user_id'  => 'integer',
        'total_minutes'         => 'integer',
        // Dates / datetimes
        'week_start_date' => 'date',
        'week_end_date'   => 'date',
        'published_at'    => 'datetime',
        'generated_at'    => 'datetime',
    ];

    // ── Labels ─────────────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'draft'     => 'Draft',
            'submitted' => 'Submitted',
            'approved'  => 'Approved',
            'published' => 'Published',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    // ── Computed helpers ───────────────────────────────────────────────────

    /**
     * Returns total_minutes formatted as "Xh Ym".
     */
    public function totalDurationForHumans(): string
    {
        $minutes = $this->total_minutes ?? 0;
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
     * Returns the week label: "30 May – 5 Jun 2026".
     */
    public function weekLabel(): string
    {
        if (! $this->week_start_date || ! $this->week_end_date) {
            return '—';
        }

        $start = $this->week_start_date->format('j M');
        $end   = $this->week_end_date->format('j M Y');
        return "{$start} – {$end}";
    }

    /**
     * Returns true if clients can see this report.
     */
    public function isPublishedToClients(): bool
    {
        return $this->status === 'published';
    }

    // ── Role-based visibility helpers ──────────────────────────────────────

    /**
     * Returns the statuses visible to the given workspace role.
     */
    public static function visibleStatusesFor(string $role): array
    {
        if (in_array($role, ['admin', 'workspace_admin', 'manager'], true)) {
            return ['draft', 'submitted', 'approved', 'published'];
        }

        if ($role === 'talent') {
            return ['submitted', 'approved', 'published'];
        }

        // Client roles: published only
        if (in_array($role, ['client_admin', 'client_staff', 'client'], true)) {
            return ['published'];
        }

        return [];
    }

    /**
     * Roles that may create or edit reports.
     */
    public static function canCreate(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
    }

    /**
     * Roles that may approve / publish reports.
     */
    public static function canApprove(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by_user_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    /** Phase 17: user who triggered auto-generation */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by_user_id');
    }

    /**
     * Whether this report was generated from workspace activity data.
     */
    public function wasGenerated(): bool
    {
        return $this->generated_at !== null;
    }
}
