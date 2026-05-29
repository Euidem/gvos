<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trial extends Model
{
    use HasFactory;

    protected $fillable = [
        'trial_code',
        'lead_request_id',
        'active_lead_user_id',
        'assigned_talent_user_id',
        'assigned_manager_user_id',
        'price_estimate_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_duration_hours',
        'trial_task_limit',
        'trial_file_limit_mb',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at'   => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function leadRequest(): BelongsTo
    {
        return $this->belongsTo(LeadRequest::class);
    }

    public function priceEstimate(): BelongsTo
    {
        return $this->belongsTo(PriceEstimate::class);
    }

    public function activeLeadUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'active_lead_user_id');
    }

    public function assignedTalent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_talent_user_id');
    }

    public function assignedManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_manager_user_id');
    }

    /** The workspace created from this trial. */
    public function workspace(): HasOne
    {
        return $this->hasOne(Workspace::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /** Returns hours remaining (0 if expired or no end date). */
    public function hoursRemaining(): float
    {
        if (! $this->ends_at || ! $this->isActive()) {
            return 0;
        }

        return max(0, now()->floatDiffInHours($this->ends_at, false));
    }
}
