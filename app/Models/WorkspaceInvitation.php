<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkspaceInvitation extends Model
{
    protected $fillable = [
        'workspace_id',
        'invited_by',
        'email',
        'name',
        'platform_role',
        'workspace_role',
        'token',
        'status',
        'expires_at',
        'accepted_at',
        'accepted_by',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (WorkspaceInvitation $invitation): void {
            if (! $invitation->token) {
                $invitation->token = static::generateToken();
            }
        });
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'revoked' => 'Revoked',
            'expired' => 'Expired',
        ];
    }

    public static function platformRoleLabels(): array
    {
        return [
            'line_manager' => 'Line Manager',
            'talent' => 'Talent',
            'individual_client' => 'Individual Client',
            'business_client_admin' => 'Business Client Admin',
            'business_client_staff' => 'Business Client Staff',
            'active_lead' => 'Active Lead',
        ];
    }

    public static function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function markExpiredIfNeeded(): bool
    {
        if ($this->status === 'pending' && $this->isExpired()) {
            $this->update(['status' => 'expired']);
            return true;
        }

        return false;
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
