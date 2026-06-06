<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'currency',
        'amount',
        'billing_cycle',
        'included_talents',
        'included_hours_per_week',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount'             => 'decimal:2',
        'included_talents'   => 'integer',
        'included_hours_per_week' => 'integer',
    ];

    // ── Labels ─────────────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'active'   => 'Active',
            'inactive' => 'Inactive',
            'archived' => 'Archived',
        ];
    }

    public static function cycleLabels(): array
    {
        return [
            'bi_weekly' => 'Bi-Weekly',
            'monthly'   => 'Monthly',
            'one_time'  => 'One-Time',
        ];
    }

    public function cycleLabel(): string
    {
        return static::cycleLabels()[$this->billing_cycle] ?? ucfirst($this->billing_cycle);
    }

    public function formattedAmount(): string
    {
        return $this->currency . ' ' . number_format((float) $this->amount, 2);
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(WorkspaceSubscription::class);
    }
}
