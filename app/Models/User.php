<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
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
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function getGvosRoleName(): string
    {
        return $this->getRoleNames()->first() ?? 'unknown';
    }

    public function getDashboardRoute(): string
    {
        return match ($this->getGvosRoleName()) {
            'super_admin', 'operations_admin' => '/admin',
            'line_manager' => '/manager/dashboard',
            'talent' => '/talent/dashboard',
            'individual_client', 'business_client_admin', 'business_client_staff' => '/client/dashboard',
            'active_lead' => '/lead/dashboard',
            default => '/',
        };
    }
}
