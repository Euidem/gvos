<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'country',
        'city',
        'timezone',
        'client_type',
        'company_name',
        'company_website',
        'company_email_domain',
        'role_needed',
        'role_needed_other',
        'estimated_hours_per_week',
        'preferred_start_date',
        'preferred_work_schedule',
        'required_skills',
        'work_description',
        'budget_range',
        'source',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'preferred_start_date' => 'date',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────

    public function priceEstimates(): HasMany
    {
        return $this->hasMany(PriceEstimate::class);
    }

    public function trials(): HasMany
    {
        return $this->hasMany(Trial::class);
    }

    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function latestEstimate(): ?PriceEstimate
    {
        return $this->priceEstimates()->latest()->first();
    }

    public function latestAcceptedEstimate(): ?PriceEstimate
    {
        return $this->priceEstimates()->where('status', 'accepted')->latest()->first();
    }

    public function latestTrial(): ?Trial
    {
        return $this->trials()->latest()->first();
    }

    // ── Computed attributes ──────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // ── Static label helpers ─────────────────────────────────────────────

    public static function roleLabels(): array
    {
        return [
            'virtual_assistant'    => 'Virtual Assistant',
            'executive_assistant'  => 'Executive Assistant',
            'social_media_manager' => 'Social Media Manager',
            'video_editor'         => 'Video Editor',
            'developer'            => 'Developer',
            'designer'             => 'Designer',
            'motion_graphics'      => 'Motion Graphics',
            'other'                => 'Other',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'new'             => 'New',
            'price_estimated' => 'Price Estimated',
            'price_accepted'  => 'Price Accepted',
            'under_review'    => 'Under Review',
            'trial_approved'  => 'Trial Approved',
            'trial_active'    => 'Trial Active',
            'trial_completed' => 'Trial Completed',
            'payment_pending' => 'Payment Pending',
            'converted'       => 'Converted',
            'lost'            => 'Lost',
            'disqualified'    => 'Disqualified',
        ];
    }
}
