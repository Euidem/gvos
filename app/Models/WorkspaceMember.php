<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceMember extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'removed_at',
        'notes',
    ];

    protected $casts = [
        'joined_at'  => 'datetime',
        'removed_at' => 'datetime',
    ];

    // ── Labels ────────────────────────────────────────────────────────────

    public static function roleLabels(): array
    {
        return [
            'client'   => 'Client',
            'talent'   => 'Talent',
            'manager'  => 'Manager',
            'observer' => 'Observer',
        ];
    }

    public function roleLabel(): string
    {
        return static::roleLabels()[$this->role] ?? ucfirst($this->role);
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
