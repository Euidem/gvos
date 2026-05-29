<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workspace extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_code',
        'lead_request_id',
        'trial_id',
        'company_id',
        'client_profile_id',
        'primary_manager_id',
        'primary_talent_id',
        'name',
        'description',
        'status',
        'type',
        'starts_at',
        'ends_at',
        'task_limit',
        'file_limit_mb',
        'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    // ── Status labels ─────────────────────────────────────────────────────

    public static function statusLabels(): array
    {
        return [
            'pending'   => 'Pending',
            'active'    => 'Active',
            'paused'    => 'Paused',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function typeLabels(): array
    {
        return [
            'trial'    => 'Trial',
            'ongoing'  => 'Ongoing',
            'project'  => 'Project',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public static function generateCode(): string
    {
        $latest = static::withTrashed()->max('id') ?? 0;
        return 'WS-' . str_pad($latest + 1, 6, '0', STR_PAD_LEFT);
    }

    // ── Relationships ─────────────────────────────────────────────────────

    public function leadRequest(): BelongsTo
    {
        return $this->belongsTo(LeadRequest::class);
    }

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function clientProfile(): BelongsTo
    {
        return $this->belongsTo(ClientProfile::class);
    }

    public function primaryManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_manager_id');
    }

    public function primaryTalent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_talent_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class)->where('status', 'active');
    }
}
