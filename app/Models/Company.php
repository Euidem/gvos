<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'type',
        'industry',
        'website',
        'country',
        'city',
        'timezone',
        'company_email_domain',
        'primary_contact_name',
        'primary_contact_email',
        'primary_contact_phone',
        'status',
        'notes',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function clientProfiles(): HasMany
    {
        return $this->hasMany(ClientProfile::class);
    }

    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    // ── Phase 8 billing ───────────────────────────────────────────────────

    public function subscriptions(): HasMany
    {
        return $this->hasMany(WorkspaceSubscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
