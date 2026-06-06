<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkspaceVaultItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'workspace_id',
        'created_by',
        'updated_by',
        'title',
        'category',
        'login_url',
        'username',
        'secret_value',
        'notes',
        'visibility',
        'status',
        'allowed_roles',
        'allowed_user_ids',
        'last_revealed_at',
        'last_revealed_by',
    ];

    protected $hidden = [
        'secret_value',
    ];

    protected $casts = [
        'workspace_id'      => 'integer',
        'created_by'        => 'integer',
        'updated_by'        => 'integer',
        'secret_value'      => 'encrypted',
        'allowed_roles'     => 'array',
        'allowed_user_ids'  => 'array',
        'last_revealed_at'  => 'datetime',
        'last_revealed_by'  => 'integer',
    ];

    public static function visibilityLabels(): array
    {
        return [
            'restricted'       => 'Restricted',
            'workspace_admins' => 'Workspace Admins & Managers',
            'assigned_users'   => 'Assigned Users',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'active'   => 'Active',
            'archived' => 'Archived',
        ];
    }

    public static function categoryLabels(): array
    {
        return [
            'login'        => 'Login',
            'server'       => 'Server',
            'app'          => 'Application',
            'api_key'      => 'API Key',
            'email'        => 'Email',
            'social_media' => 'Social Media',
            'billing'      => 'Billing',
            'other'        => 'Other',
        ];
    }

    public static function allowedRoleOptions(): array
    {
        return [
            'workspace_admin' => 'Workspace Admin',
            'manager'         => 'Manager',
            'client_admin'    => 'Client Admin',
            'client_staff'    => 'Client Staff',
            'talent'          => 'Talent',
            'assigned_user'   => 'Assigned User',
        ];
    }

    public function visibilityLabel(): string
    {
        return static::visibilityLabels()[$this->visibility] ?? ucfirst(str_replace('_', ' ', $this->visibility));
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function categoryLabel(): string
    {
        return static::categoryLabels()[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category ?? 'other'));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function allowedRoleValues(): array
    {
        return collect($this->allowed_roles ?? [])
            ->filter()
            ->values()
            ->all();
    }

    public function allowedUserIdValues(): array
    {
        return collect($this->allowed_user_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
    }

    public static function canCreateForRole(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager', 'client_admin'], true);
    }

    public static function canManageForRole(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
    }

    public static function canUseVaultRole(string $role): bool
    {
        return ! in_array($role, ['none', 'observer'], true);
    }

    public function canManage(User $user, ?string $role = null): bool
    {
        $role ??= $this->workspace?->resolveUserWorkspaceRole($user) ?? 'none';

        if (static::canManageForRole($role)) {
            return true;
        }

        return $role === 'client_admin'
            && $this->created_by !== null
            && (int) $this->created_by === (int) $user->id;
    }

    public function canViewMetadata(User $user, ?string $role = null): bool
    {
        $role ??= $this->workspace?->resolveUserWorkspaceRole($user) ?? 'none';

        if ($this->canManage($user, $role)) {
            return true;
        }

        if (! $this->isActive()) {
            return false;
        }

        if ($this->visibility === 'workspace_admins') {
            return in_array($role, ['admin', 'workspace_admin', 'manager'], true);
        }

        return $this->isAllowedByRoleOrUser($user, $role);
    }

    public function canReveal(User $user, ?string $role = null): bool
    {
        $role ??= $this->workspace?->resolveUserWorkspaceRole($user) ?? 'none';

        if (! $this->isActive()) {
            return false;
        }

        if (in_array($role, ['admin', 'workspace_admin'], true)) {
            return true;
        }

        if ($this->created_by !== null && (int) $this->created_by === (int) $user->id) {
            return in_array($role, ['manager', 'client_admin'], true);
        }

        if ($role === 'manager' && $this->visibility === 'workspace_admins') {
            return true;
        }

        return $this->isAllowedByRoleOrUser($user, $role);
    }

    public function canViewAccessLogs(User $user, ?string $role = null): bool
    {
        $role ??= $this->workspace?->resolveUserWorkspaceRole($user) ?? 'none';

        if (in_array($role, ['admin', 'workspace_admin', 'manager'], true)) {
            return true;
        }

        return $role === 'client_admin'
            && $this->created_by !== null
            && (int) $this->created_by === (int) $user->id;
    }

    public function isAllowedByRoleOrUser(User $user, string $role): bool
    {
        return in_array($role, $this->allowedRoleValues(), true)
            || in_array((int) $user->id, $this->allowedUserIdValues(), true);
    }

    public static function queryForUser(Workspace $workspace, User $user, ?string $role = null): Builder
    {
        $role ??= $workspace->resolveUserWorkspaceRole($user);

        $query = static::query()->where('workspace_id', $workspace->id);

        if (static::canManageForRole($role)) {
            return $query;
        }

        if ($role === 'client_admin') {
            return $query->where(function (Builder $query) use ($user, $role) {
                $query->where('created_by', $user->id)
                    ->orWhere(function (Builder $query) use ($user, $role) {
                        $query->where('status', 'active')
                            ->where(function (Builder $query) use ($user, $role) {
                                $query->whereJsonContains('allowed_roles', $role)
                                    ->orWhereJsonContains('allowed_user_ids', (int) $user->id);
                            });
                    });
            });
        }

        if (! static::canUseVaultRole($role)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('status', 'active')
            ->where(function (Builder $query) use ($user, $role) {
                $query->whereJsonContains('allowed_roles', $role)
                    ->orWhereJsonContains('allowed_user_ids', (int) $user->id);
            });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function lastRevealedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_revealed_by');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(WorkspaceVaultAccessLog::class)->orderByDesc('created_at');
    }
}
