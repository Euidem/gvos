<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'country',
        'city',
        'bio',
        'onboarding_status',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Full name derived from first + last, fallback to user.name */
    public function getFullNameAttribute(): string
    {
        $full = trim("{$this->first_name} {$this->last_name}");
        return $full ?: ($this->user?->name ?? '');
    }
}
