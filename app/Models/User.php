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

    /** Notification delivery preferences for this user. */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    public function sentWorkspaceInvitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class, 'invited_by');
    }

    public function acceptedWorkspaceInvitations(): HasMany
    {
        return $this->hasMany(WorkspaceInvitation::class, 'accepted_by');
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

    // ── Onboarding helpers ───────────────────────────────────────────────

    /**
     * Returns true if the user still needs to complete onboarding.
     * Based on user_profiles.onboarding_status.
     */
    public function needsOnboarding(): bool
    {
        $profile = $this->profile;
        return ! $profile || $profile->onboarding_status !== 'complete';
    }

    /**
     * Returns true when the required profile fields (first + last name) are filled.
     */
    public function hasCompletedRequiredProfile(): bool
    {
        $profile = $this->profile;
        return $profile
            && filled($profile->first_name)
            && filled($profile->last_name);
    }

    /**
     * Returns the role-specific profile model: TalentProfile, ManagerProfile, or ClientProfile.
     */
    public function profileForRole(): ?\Illuminate\Database\Eloquent\Model
    {
        return match ($this->getGvosRoleName()) {
            'talent'                                                              => $this->talentProfile,
            'line_manager'                                                        => $this->managerProfile,
            'individual_client', 'business_client_admin', 'business_client_staff' => $this->clientProfile,
            default                                                               => null,
        };
    }

    /**
     * Returns the user's first active workspace (membership first, then primary roles).
     */
    public function primaryWorkspace(): ?Workspace
    {
        $membership = $this->workspaceMemberships()
            ->where('status', 'active')
            ->with('workspace')
            ->first();

        if ($membership?->workspace) {
            return $membership->workspace;
        }

        return Workspace::where(function ($q) {
            $q->where('primary_manager_id', $this->id)
              ->orWhere('primary_talent_id', $this->id);
        })->whereIn('status', ['pending', 'active'])->first();
    }

    /**
     * Returns a role-tailored onboarding checklist.
     * Each item: ['key', 'label', 'done', 'optional' (default false)]
     */
    public function onboardingChecklist(): array
    {
        $profile    = $this->profile;
        $role       = $this->getGvosRoleName();
        $hasName    = $profile && filled($profile->first_name) && filled($profile->last_name);
        $hasPhone   = $profile && filled($profile->phone);
        $hasTz      = filled($this->timezone) && $this->timezone !== 'UTC';
        $workspace  = $this->primaryWorkspace();
        $hasWs      = $workspace !== null;

        $items = [
            ['key' => 'name',      'label' => 'Complete your full name',       'done' => $hasName],
            ['key' => 'timezone',  'label' => 'Set your timezone',             'done' => $hasTz],
            ['key' => 'phone',     'label' => 'Add a phone number',            'done' => $hasPhone,  'optional' => true],
            ['key' => 'workspace', 'label' => 'Join or access your workspace', 'done' => $hasWs],
        ];

        $roleItems = match ($role) {
            'talent' => [
                ['key' => 'tasks', 'label' => 'Check your assigned tasks',
                 'done' => WorkspaceTask::where('assigned_to_user_id', $this->id)->exists(),
                 'optional' => true],
            ],
            'line_manager' => [
                ['key' => 'team', 'label' => 'Review your team members',
                 'done' => $hasWs, 'optional' => true],
                ['key' => 'timelogs', 'label' => 'Review submitted time logs',
                 'done' => $hasWs && WorkspaceTimeLog::whereIn('workspace_id',
                        Workspace::where('primary_manager_id', $this->id)->pluck('id'))
                    ->where('status', 'submitted')->exists(),
                 'optional' => true],
            ],
            'individual_client', 'business_client_admin' => [
                ['key' => 'tasks', 'label' => 'Review workspace tasks',
                 'done' => $hasWs, 'optional' => true],
            ],
            'business_client_staff' => [
                ['key' => 'messages', 'label' => 'Check workspace messages',
                 'done' => $hasWs, 'optional' => true],
            ],
            default => [],
        };

        return array_merge($items, $roleItems);
    }

    /**
     * Returns a 0–100 completion percentage based on required checklist items.
     */
    public function onboardingCompletionPercentage(): int
    {
        $checklist = $this->onboardingChecklist();
        $required  = array_values(array_filter($checklist, fn ($i) => empty($i['optional'])));
        $done      = array_filter($required, fn ($i) => $i['done']);
        $total     = count($required);

        if ($total === 0) {
            return 100;
        }

        return (int) round(count($done) / $total * 100);
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
     * Single source of truth for GVOS admin (Filament) panel access.
     * Only super_admin and operations_admin may enter the GVOS Ops Console.
     */
    public function canAccessAdminPanel(): bool
    {
        return $this->hasAnyRole(['super_admin', 'operations_admin']);
    }

    /**
     * Filament v3 panel access gate. Delegates to canAccessAdminPanel()
     * so the admin gate has one definition shared with the login redirect logic.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessAdminPanel();
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
