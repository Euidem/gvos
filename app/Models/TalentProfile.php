<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'talent_code',
        'role_type',
        'skill_summary',
        'availability_type',
        'weekly_capacity_hours',
        'work_timezone',
        'training_status',
        'equipment_status',
        'internal_notes',
        'status',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
