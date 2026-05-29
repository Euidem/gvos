<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceEstimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_request_id',
        'currency',
        'estimated_amount',
        'billing_cycle',
        'estimated_hours_per_week',
        'role_needed',
        'notes',
        'status',
        'accepted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'estimated_amount' => 'decimal:2',
            'accepted_at'      => 'datetime',
            'expires_at'       => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function leadRequest(): BelongsTo
    {
        return $this->belongsTo(LeadRequest::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function formattedAmount(): string
    {
        return $this->currency . ' ' . number_format((float) $this->estimated_amount, 2);
    }
}
