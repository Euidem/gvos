<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

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
        // Phase 18: billing enforcement fields
        'restricted_at',
        'suspended_at',
        'reactivated_at',
        'restriction_reason',
        'suspended_by',
        'reactivated_by',
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
        // Phase 18
        'restricted_at'      => 'datetime',
        'suspended_at'       => 'datetime',
        'reactivated_at'     => 'datetime',
        'suspended_by'       => 'integer',
        'reactivated_by'     => 'integer',
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

    // ── Status constants ────────────────────────────────────────────────────

    /** Grace period (days) after the next_billing_date before restriction applies. */
    public const GRACE_PERIOD_DAYS = 3;

    /** Due-soon warning window (days) before next_billing_date. */
    public const DUE_SOON_DAYS = 3;

    // ── Status helpers ──────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isPaymentDue(): bool
    {
        return $this->status === 'payment_due';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    /**
     * Returns true if the subscription is suspended (manual admin action).
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Returns true if the subscription has been restricted (post-grace-period,
     * not yet manually suspended). Derived from restricted_at timestamp.
     */
    public function isRestricted(): bool
    {
        return $this->restricted_at !== null && $this->suspended_at === null;
    }

    /**
     * Returns true if the suspension was performed manually by an admin user
     * (as opposed to automated status transitions).
     */
    public function wasManuallySuspended(): bool
    {
        return $this->suspended_at !== null && $this->suspended_by !== null;
    }

    /**
     * Returns true if the subscription is within its grace period.
     * Grace period is active when status=overdue AND grace_ends_at is in the future.
     */
    public function isWithinGracePeriod(): bool
    {
        if (! $this->isOverdue()) {
            return false;
        }

        if ($this->grace_ends_at !== null) {
            return $this->grace_ends_at->isFuture();
        }

        // If no explicit grace_ends_at, derive from billing date + grace days
        if ($this->next_billing_date) {
            $graceEnd = $this->next_billing_date->addDays(self::GRACE_PERIOD_DAYS);
            return now()->lt($graceEnd);
        }

        return false;
    }

    /**
     * Returns true if the subscription is overdue AND outside the grace period.
     * This is the trigger to move to restricted state.
     */
    public function shouldBeRestricted(): bool
    {
        if (! $this->isOverdue()) {
            return false;
        }
        return ! $this->isWithinGracePeriod();
    }

    public function requiresPayment(): bool
    {
        return in_array($this->status, ['payment_due', 'overdue'], true);
    }

    /**
     * Number of days until the next billing date.
     * Returns null if next_billing_date is not set.
     * Negative values mean the date has passed.
     */
    public function daysUntilDue(): ?int
    {
        if (! $this->next_billing_date) {
            return null;
        }
        return (int) now()->startOfDay()->diffInDays($this->next_billing_date, false);
    }

    /**
     * Number of days the subscription is overdue.
     * Returns 0 if not overdue or no billing date.
     */
    public function daysOverdue(): int
    {
        if (! $this->isOverdue() || ! $this->next_billing_date) {
            return 0;
        }
        $days = (int) $this->next_billing_date->diffInDays(now(), false);
        return max(0, $days);
    }

    /**
     * Returns true if payment is due within DUE_SOON_DAYS days.
     */
    public function isDueSoon(): bool
    {
        if (! in_array($this->status, ['active', 'trial', 'payment_due'], true)) {
            return false;
        }
        $days = $this->daysUntilDue();
        return $days !== null && $days >= 0 && $days <= self::DUE_SOON_DAYS;
    }

    /**
     * Human-readable billing status label for UI display.
     * Accounts for restriction state beyond the base status.
     */
    public function billingStatusLabel(): string
    {
        if ($this->isSuspended()) {
            return 'Suspended';
        }
        if ($this->isRestricted()) {
            return 'Access Restricted';
        }
        if ($this->isOverdue()) {
            return $this->isWithinGracePeriod() ? 'Overdue (Grace Period)' : 'Overdue';
        }
        if ($this->isDueSoon()) {
            return 'Due Soon';
        }
        return $this->statusLabel();
    }

    /**
     * Hex color for billing status UI badges.
     */
    public function billingStatusColor(): string
    {
        if ($this->isSuspended()) {
            return '#64748B';
        }
        if ($this->isRestricted()) {
            return '#DC2626';
        }
        if ($this->isOverdue()) {
            return $this->isWithinGracePeriod() ? '#F59E0B' : '#EF4444';
        }
        if ($this->isDueSoon()) {
            return '#D97706';
        }
        return match ($this->status) {
            'active'      => '#10B981',
            'trial'       => '#8B5CF6',
            'payment_due' => '#F59E0B',
            'cancelled'   => '#94A3B8',
            'ended'       => '#94A3B8',
            default       => '#94A3B8',
        };
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

    public function suspendedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function reactivatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reactivated_by');
    }
}
