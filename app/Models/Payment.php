<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
// Note: Invoice is in the same namespace (App\Models), no import needed.

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payment_reference',
        'invoice_id',
        'workspace_id',
        'workspace_subscription_id',
        'provider',
        'provider_reference',
        'currency',
        'amount',
        'status',
        'paid_at',
        'confirmed_by_user_id',
        'confirmation_notes',
        'raw_payload',
    ];

    protected $casts = [
        'invoice_id'                => 'integer',
        'workspace_id'              => 'integer',
        'workspace_subscription_id' => 'integer',
        'confirmed_by_user_id'      => 'integer',
        'amount'                    => 'decimal:2',
        'paid_at'                   => 'datetime',
        'raw_payload'               => 'array',
    ];

    // ── Auto-generate payment reference ────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            if (empty($payment->payment_reference)) {
                $payment->payment_reference = 'GVOS-PAY-' . strtoupper(Str::random(10));
            }
        });
    }

    // ── Labels ─────────────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'pending'   => 'Pending',
            'confirmed' => 'Confirmed',
            'failed'    => 'Failed',
            'reversed'  => 'Reversed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function providerLabels(): array
    {
        return [
            'manual'        => 'Manual',
            'bank_transfer' => 'Bank Transfer',
            'fincra'        => 'Fincra',
            'flutterwave'   => 'Flutterwave',
            'paystack'      => 'Paystack',
            'stripe'        => 'Stripe',
            'other'         => 'Other',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function providerLabel(): string
    {
        return static::providerLabels()[$this->provider] ?? ucfirst($this->provider);
    }

    // ── Confirm payment and update invoice ─────────────────────────────────

    /**
     * Confirm this payment:
     *  1. Set status = confirmed, paid_at = now()
     *  2. Apply to invoice (increment amount_paid, recalc balance_due, set paid/partial)
     *  3. Update subscription last_paid_at if linked
     */
    public function confirm(int $confirmedByUserId, string $notes = ''): void
    {
        if ($this->status === 'confirmed' && $this->confirmed_by_user_id && $this->paid_at) {
            return;
        }

        if ($this->invoice_id && $this->invoice) {
            $this->workspace_id = $this->workspace_id ?: $this->invoice->workspace_id;
            $this->workspace_subscription_id = $this->workspace_subscription_id ?: $this->invoice->workspace_subscription_id;
            $this->currency = $this->currency ?: $this->invoice->currency;
        }

        $this->status               = 'confirmed';
        $this->paid_at              = now();
        $this->confirmed_by_user_id = $confirmedByUserId;
        if ($notes) {
            $this->confirmation_notes = $notes;
        }
        $this->save();

        // Apply to invoice
        if ($this->invoice_id && $this->invoice) {
            $this->invoice->applyPayment((float) $this->amount);
        }

        // Update subscription
        if ($this->workspace_subscription_id && $this->subscription) {
            $sub = $this->subscription;
            $sub->last_paid_at = now();

            // Phase 18: Only auto-restore payment_due and overdue statuses.
            // Manual suspensions (suspended_by IS NOT NULL) require admin reactivation.
            // After payment, also clear restriction tracking fields if relevant.
            if (in_array($sub->status, ['payment_due', 'overdue'], true)) {
                // Check if there are still outstanding invoices on this workspace
                $stillOwed = $sub->workspace
                    ? Invoice::where('workspace_subscription_id', $sub->id)
                        ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
                        ->where('balance_due', '>', 0)
                        ->exists()
                    : false;

                if (! $stillOwed) {
                    $sub->status        = 'active';
                    $sub->restricted_at = null;
                    $sub->reactivated_at = now();
                }
            } elseif ($sub->status === 'suspended' && ! $sub->wasManuallySuspended()) {
                // Auto-suspended (no suspended_by set): safe to auto-restore
                $sub->status         = 'active';
                $sub->restricted_at  = null;
                $sub->suspended_at   = null;
                $sub->reactivated_at = now();
            }
            // Manual suspension (suspended_by IS NOT NULL) is NOT auto-cleared here.
            // Admin must explicitly reactivate via Filament.

            $sub->save();
        }
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WorkspaceSubscription::class, 'workspace_subscription_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by_user_id');
    }
}
