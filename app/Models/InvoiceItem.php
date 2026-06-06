<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_amount',
        'total_amount',
        'item_type',
    ];

    protected $casts = [
        'invoice_id'   => 'integer',
        'quantity'     => 'decimal:4',
        'unit_amount'  => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Auto-calculate total_amount on create/update when not explicitly set.
     */
    protected static function booted(): void
    {
        $calc = function (InvoiceItem $item) {
            if (! isset($item->total_amount) || (float) $item->total_amount === 0.0) {
                $item->total_amount = round((float) $item->quantity * (float) $item->unit_amount, 2);
            }
        };

        static::creating($calc);
        static::updating($calc);

        static::saved(function (InvoiceItem $item) {
            $item->invoice?->recalculateTotals();
            $item->invoice?->save();
        });

        static::deleted(function (InvoiceItem $item) {
            $item->invoice?->recalculateTotals();
            $item->invoice?->save();
        });
    }

    // ── Labels ─────────────────────────────────────────────────────────────

    public static function typeLabels(): array
    {
        return [
            'subscription'  => 'Subscription',
            'extra_hours'   => 'Extra Hours',
            'setup_fee'     => 'Setup Fee',
            'adjustment'    => 'Adjustment',
            'other'         => 'Other',
        ];
    }

    public function typeLabel(): string
    {
        return static::typeLabels()[$this->item_type] ?? ucfirst($this->item_type);
    }

    // ── Relationships ──────────────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
