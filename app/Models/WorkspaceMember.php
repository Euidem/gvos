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

    /**
     * Friendly display labels for all workspace member roles.
     *
     * workspace_admin — designated workspace-level admin (full task control)
     * manager         — GVOS line manager overseeing the workspace
     * talent          — talent/contractor doing the work
     * client_admin    — individual client or business client admin (can review/approve)
     * client_staff    — business client staff (view + comment only)
     * client          — legacy; treated as client_admin in application code
     * observer        — read-only access, no task interaction
     */
    public static function roleLabels(): array
    {
        return [
            'workspace_admin' => 'Workspace Admin',
            'manager'         => 'Manager',
            'talent'          => 'Talent',
            'client_admin'    => 'Client Admin',
            'client_staff'    => 'Client Staff',
            'client'          => 'Client',
            'observer'        => 'Observer',
        ];
    }

    public function roleLabel(): string
    {
        return static::roleLabels()[$this->role] ?? ucfirst(str_replace('_', ' ', $this->role));
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
