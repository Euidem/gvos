<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'workspace_id',
        'workspace_subscription_id',
        'client_profile_id',
        'company_id',
        'currency',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'status',
        'issue_date',
        'due_date',
        'paid_at',
        'notes',
        'internal_notes',
    ];

    protected $casts = [
        'workspace_id'              => 'integer',
        'workspace_subscription_id' => 'integer',
        'client_profile_id'         => 'integer',
        'company_id'                => 'integer',
        'subtotal'                  => 'decimal:2',
        'discount_amount'           => 'decimal:2',
        'tax_amount'                => 'decimal:2',
        'total_amount'              => 'decimal:2',
        'amount_paid'               => 'decimal:2',
        'balance_due'               => 'decimal:2',
        'issue_date'                => 'date',
        'due_date'                  => 'date',
        'paid_at'                   => 'datetime',
    ];

    // ── Invoice number generation ───────────────────────────────────────────

    /**
     * Generate a unique invoice number in format: GVOS-INV-YYYYMM-0001
     * Increments from the highest existing invoice for this month.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'GVOS-INV-' . now()->format('Ym') . '-';

        // Find the highest sequence for this month
        $last = static::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $seq   = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Auto-generate invoice_number on create if not set.
     */
    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    // ── Labels ─────────────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'draft'          => 'Draft',
            'issued'         => 'Issued',
            'partially_paid' => 'Partially Paid',
            'paid'           => 'Paid',
            'overdue'        => 'Overdue',
            'cancelled'      => 'Cancelled',
            'void'           => 'Void',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    // ── Computed helpers ────────────────────────────────────────────────────

    /**
     * Recalculate totals from items, discount, and tax.
     */
    public function recalculateTotals(): void
    {
        $itemsSubtotal = (float) $this->items()->sum('total_amount');

        if ($itemsSubtotal > 0) {
            $this->subtotal = $itemsSubtotal;
            $this->total_amount = max(0,
                (float) $this->subtotal
                - (float) $this->discount_amount
                + (float) $this->tax_amount
            );
        } elseif ((float) $this->subtotal > 0) {
            $this->total_amount = max(0,
                (float) $this->subtotal
                - (float) $this->discount_amount
                + (float) $this->tax_amount
            );
        } else {
            // Manual invoices may be created without line items or subtotal.
            // In that case, preserve the entered total and only refresh balance_due.
            $this->total_amount = max(0, (float) $this->total_amount);
        }

        $this->balance_due = max(0,
            (float) $this->total_amount - (float) $this->amount_paid
        );
    }

    /**
     * Record a payment against this invoice and update statuses.
     * Called after a payment is confirmed.
     */
    public function applyPayment(float $amount): void
    {
        $this->amount_paid = (float) $this->amount_paid + $amount;
        $this->balance_due = max(0, (float) $this->total_amount - (float) $this->amount_paid);

        if ($this->balance_due <= 0) {
            $this->status  = 'paid';
            $this->paid_at = now();
        } elseif ((float) $this->amount_paid > 0) {
            $this->status = 'partially_paid';
        }

        $this->save();
    }

    public function isClientVisible(): bool
    {
        return ! in_array($this->status, ['void'], true);
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(WorkspaceSubscription::class, 'workspace_subscription_id');
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('created_at');
    }
}
