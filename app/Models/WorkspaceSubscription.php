<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceSubscription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'billing_plan_id',
        'client_profile_id',
        'company_id',
        'currency',
        'amount',
        'billing_cycle',
        'status',
        'starts_at',
        'next_billing_date',
        'ends_at',
        'last_paid_at',
        'grace_ends_at',
        'notes',
    ];

    protected $casts = [
        'workspace_id'       => 'integer',
        'billing_plan_id'    => 'integer',
        'client_profile_id'  => 'integer',
        'company_id'         => 'integer',
        'amount'             => 'decimal:2',
        'starts_at'          => 'date',
        'next_billing_date'  => 'date',
        'ends_at'            => 'date',
        'last_paid_at'       => 'datetime',
        'grace_ends_at'      => 'datetime',
    ];

    // ── Labels ─────────────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'trial'       => 'Trial',
            'active'      => 'Active',
            'payment_due' => 'Payment Due',
            'overdue'     => 'Overdue',
            'suspended'   => 'Suspended',
            'cancelled'   => 'Cancelled',
            'ended'       => 'Ended',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function formattedAmount(): string
    {
        return $this->currency . ' ' . number_format((float) $this->amount, 2);
    }

    public function cycleLabel(): string
    {
        return BillingPlan::cycleLabels()[$this->billing_cycle] ?? ucfirst($this->billing_cycle);
    }

    // ── Status helpers ──────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function requiresPayment(): bool
    {
        return in_array($this->status, ['payment_due', 'overdue'], true);
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function billingPlan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class);
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
