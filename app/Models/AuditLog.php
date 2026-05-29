<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /**
     * Audit logs are immutable — no update_at column.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'context',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'context'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────

    /** The user who performed the action (null = system). */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** The model that was acted upon. */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Boot ─────────────────────────────────────────────────────────────

    protected static function booting(): void
    {
        // Prevent any updates or deletes at the application level.
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }
}
