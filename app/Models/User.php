<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /** Trials where this user is the active lead. */
    public function activeLeadTrials(): HasMany
    {
        return $this->hasMany(Trial::class, 'active_lead_user_id');
    }

    /** Trials where this user is the assigned talent. */
    public function assignedTalentTrials(): HasMany
    {
        return $this->hasMany(Trial::class, 'assigned_talent_user_id');
    }

    /** Trials where this user is the assigned manager. */
    public function assignedManagerTrials(): HasMany
    {
        return $this->hasMany(Trial::class, 'assigned_manager_user_id');
    }

    /** All workspace memberships for this user. */
    public function workspaceMemberships(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /** Workspaces where this user is the primary manager. */
    public function managedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'primary_manager_id');
    }

    /** Workspaces where this user is the primary talent. */
    public function talentWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'primary_talent_id');
    }

    /** Tasks created by this user. */
    public function createdWorkspaceTasks(): HasMany
    {
        return $this->hasMany(WorkspaceTask::class, 'created_by_user_id');
    }

    /** Tasks assigned to this user. */
    public function assignedWorkspaceTasks(): HasMany
    {
        return $this->hasMany(WorkspaceTask::class, 'assigned_to_user_id');
    }

    /** Task comments authored by this user. */
    public function workspaceTaskComments(): HasMany
    {
        return $this->hasMany(WorkspaceTaskComment::class);
    }

    /** Workspace chat messages posted by this user. */
    public function workspaceMessages(): HasMany
    {
        return $this->hasMany(WorkspaceMessage::class);
    }

    /** Workspace files uploaded by this user. */
    public function workspaceFiles(): HasMany
    {
        return $this->hasMany(WorkspaceFile::class, 'uploaded_by_user_id');
    }

    /** Vault items created by this user. */
    public function createdVaultItems(): HasMany
    {
        return $this->hasMany(WorkspaceVaultItem::class, 'created_by');
    }

    /** Vault access events performed by this user. */
    public function workspaceVaultAccessLogs(): HasMany
    {
        return $this->hasMany(WorkspaceVaultAccessLog::class);
    }

    // ── Phase 7 relationships ────────────────────────────────────────────

    /** Time logs submitted by this user. */
    public function workspaceTimeLogs(): HasMany
    {
        return $this->hasMany(WorkspaceTimeLog::class);
    }

    /** Time logs reviewed by this user (as manager/admin). */
    public function reviewedWorkspaceTimeLogs(): HasMany
    {
        return $this->hasMany(WorkspaceTimeLog::class, 'reviewed_by_user_id');
    }

    /** Weekly reports prepared by this user. */
    public function preparedWeeklyReports(): HasMany
    {
        return $this->hasMany(WorkspaceWeeklyReport::class, 'prepared_by_user_id');
    }

    /** Weekly reports reviewed by this user. */
    public function reviewedWeeklyReports(): HasMany
    {
        return $this->hasMany(WorkspaceWeeklyReport::class, 'reviewed_by_user_id');
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
