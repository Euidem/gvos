<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'status',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    public function talentProfile(): HasOne
    {
        return $this->hasOne(TalentProfile::class);
    }

    public function managerProfile(): HasOne
    {
        return $this->hasOne(ManagerProfile::class);
    }

    // ── Status helpers ───────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** Returns true for any status that should block portal access. */
    public function isAccessBlocked(): bool
    {
        return in_array($this->status, ['suspended', 'inactive']);
    }

    // ── Role helpers ─────────────────────────────────────────────────────

    public function getGvosRoleName(): string
    {
        return $this->getRoleNames()->first() ?? 'unknown';
    }

    // ── Filament ─────────────────────────────────────────────────────────

    /**
     * Filament v3 panel access gate.
     * Only super_admin and operations_admin may enter the GVOS Ops Console.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super_admin', 'operations_admin']);
    }

    // ── Routing ─────────────────────────────────────────────────────────

    public function getDashboardRoute(): string
    {
        return match ($this->getGvosRoleName()) {
            'super_admin', 'operations_admin'                                     => '/admin',
            'line_manager'                                                         => '/manager/dashboard',
            'talent'                                                                => '/talent/dashboard',
            'individual_client', 'business_client_admin', 'business_client_staff' => '/client/dashboard',
            'active_lead'                                                          => '/lead/dashboard',
            default                                                                => '/',
        };
    }
}
