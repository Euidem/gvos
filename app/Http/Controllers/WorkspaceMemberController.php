<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkspaceMemberController extends Controller
{
    private const WORKSPACE_ROLES = [
        'workspace_admin',
        'client_admin',
        'client_staff',
        'manager',
        'talent',
        'observer',
    ];

    public function index(Request $request, Workspace $workspace)
    {
        $role = $this->requireMemberPageAccess($request->user(), $workspace);

        $workspace->load([
            'company',
            'primaryManager',
            'primaryTalent',
            'activeMembers.user.roles',
            'invitations.inviter',
        ]);

        $canManageMembers = $this->canManageMembers($role);
        $canInvite = $this->canInviteMembers($role);
        $allowedRoles = $this->allowedRolesForActor($role);

        $availableUsers = $canManageMembers
            ? User::query()
                ->with(['roles', 'clientProfile', 'talentProfile', 'managerProfile'])
                ->whereNotIn('status', ['suspended', 'inactive'])
                ->orderBy('name')
                ->limit(500)
                ->get()
            : collect();

        $invitations = $canInvite
            ? $workspace->invitations()->with('inviter')->latest()->get()
            : collect();

        return view('workspace.members.index', compact(
            'workspace',
            'role',
            'canManageMembers',
            'canInvite',
            'allowedRoles',
            'availableUsers',
            'invitations'
        ));
    }

    public function invite(Request $request, Workspace $workspace)
    {
        $role = $this->requireMemberPageAccess($request->user(), $workspace);

        if (! $this->canInviteMembers($role)) {
            abort(403, 'You cannot invite members to this workspace.');
        }

        $allowedRoles = $this->allowedRolesForActor($role);

        return view('workspace.members.invite', compact('workspace', 'role', 'allowedRoles'));
    }

    public function store(Request $request, Workspace $workspace)
    {
        $actor = $request->user();
        $actorRole = $this->requireMemberPageAccess($actor, $workspace);

        if (! $this->canManageMembers($actorRole)) {
            abort(403, 'You cannot add workspace members.');
        }

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'workspace_role' => ['required', Rule::in($this->allowedRolesForActor($actorRole))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = User::with(['roles', 'clientProfile', 'talentProfile', 'managerProfile'])
            ->findOrFail($validated['user_id']);

        $this->ensureTargetAllowed($actor, $actorRole, $workspace, $user, $validated['workspace_role']);

        $existing = $workspace->members()->where('user_id', $user->id)->first();

        if ($existing && $existing->status === 'active') {
            throw ValidationException::withMessages([
                'user_id' => 'This user is already an active member of the workspace.',
            ]);
        }

        $member = DB::transaction(function () use ($workspace, $user, $validated, $existing) {
            if ($existing) {
                $existing->update([
                    'role' => $validated['workspace_role'],
                    'status' => 'active',
                    'joined_at' => $existing->joined_at ?? now(),
                    'removed_at' => null,
                    'notes' => $validated['notes'] ?? $existing->notes,
                ]);

                return $existing->fresh(['workspace', 'user']);
            }

            return WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => $validated['workspace_role'],
                'status' => 'active',
                'joined_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ])->fresh(['workspace', 'user']);
        });

        AuditLogger::workspaceMembershipAdded($workspace, $member, ['source' => 'portal']);
        AuditLogger::workspaceMemberAdded($workspace, $member, ['source' => 'portal']);
        app(NotificationService::class)->notifyWorkspaceMemberAdded($member, $actor);

        return redirect()
            ->route('workspace.members.index', $workspace)
            ->with('success', 'Workspace member added.');
    }

    public function update(Request $request, Workspace $workspace, WorkspaceMember $member)
    {
        $this->authorizeMemberBelongsToWorkspace($workspace, $member);

        $actor = $request->user();
        $actorRole = $this->requireMemberPageAccess($actor, $workspace);

        if (! $this->canManageMembers($actorRole)) {
            abort(403, 'You cannot update workspace members.');
        }

        $validated = $request->validate([
            'workspace_role' => ['required', Rule::in($this->allowedRolesForActor($actorRole))],
        ]);

        $member->loadMissing('user.roles', 'user.clientProfile', 'user.talentProfile', 'user.managerProfile');
        $this->ensureTargetAllowed($actor, $actorRole, $workspace, $member->user, $validated['workspace_role']);

        $oldRole = $member->role;
        if ($oldRole !== $validated['workspace_role']) {
            $member->update(['role' => $validated['workspace_role']]);
            $member = $member->fresh(['workspace', 'user']);

            AuditLogger::workspaceMemberRoleChanged($workspace, $member, $oldRole, $member->role, ['source' => 'portal']);
            AuditLogger::workspaceMemberUpdated($workspace, $member, [
                'source' => 'portal',
                'from' => $oldRole,
                'to' => $member->role,
            ]);
            app(NotificationService::class)->notifyWorkspaceMemberRoleChanged($member, $oldRole, $actor);
        }

        return redirect()
            ->route('workspace.members.index', $workspace)
            ->with('success', 'Workspace member role updated.');
    }

    public function deactivate(Request $request, Workspace $workspace, WorkspaceMember $member)
    {
        $this->authorizeMemberBelongsToWorkspace($workspace, $member);

        $actor = $request->user();
        $actorRole = $this->requireMemberPageAccess($actor, $workspace);

        if (! $this->canManageMembers($actorRole)) {
            abort(403, 'You cannot remove workspace members.');
        }

        if ($member->status === 'active') {
            $member->update([
                'status' => 'removed',
                'removed_at' => now(),
            ]);

            $member = $member->fresh(['workspace', 'user']);
            AuditLogger::workspaceMemberDeactivated($workspace, $member, ['source' => 'portal']);
            AuditLogger::workspaceMemberRemoved($workspace, $member, ['source' => 'portal']);
            app(NotificationService::class)->notifyWorkspaceMemberDeactivated($member, $actor);
        }

        return redirect()
            ->route('workspace.members.index', $workspace)
            ->with('success', 'Workspace member deactivated.');
    }

    public function sendInvitation(Request $request, Workspace $workspace)
    {
        $actor = $request->user();
        $actorRole = $this->requireMemberPageAccess($actor, $workspace);

        if (! $this->canInviteMembers($actorRole)) {
            abort(403, 'You cannot invite workspace members.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'platform_role' => ['nullable', Rule::in(array_keys(WorkspaceInvitation::platformRoleLabels()))],
            'workspace_role' => ['required', Rule::in($this->allowedRolesForActor($actorRole))],
        ]);

        $email = strtolower($validated['email']);
        $existingUser = User::query()
            ->with(['roles', 'clientProfile', 'talentProfile', 'managerProfile'])
            ->where('email', $email)
            ->first();

        if ($existingUser) {
            $this->ensureTargetAllowed($actor, $actorRole, $workspace, $existingUser, $validated['workspace_role']);
        } else {
            $this->ensureInviteEmailAllowed($actorRole, $workspace, $email, $validated['workspace_role']);
        }

        if ($existingUser && $workspace->activeMembers()->where('user_id', $existingUser->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email already belongs to an active workspace member.',
            ]);
        }

        $pendingExists = $workspace->invitations()
            ->where('email', $email)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($pendingExists) {
            throw ValidationException::withMessages([
                'email' => 'A pending invitation already exists for this email.',
            ]);
        }

        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'invited_by' => $actor->id,
            'email' => $email,
            'name' => $validated['name'] ?? null,
            'platform_role' => $validated['platform_role'] ?? null,
            'workspace_role' => $validated['workspace_role'],
            'status' => 'pending',
            'expires_at' => now()->addDays(14),
        ]);

        AuditLogger::workspaceInvitationCreated($invitation, ['source' => 'portal']);
        app(NotificationService::class)->notifyWorkspaceInvitationSent($invitation, $actor);

        return redirect()
            ->route('workspace.members.index', $workspace)
            ->with('success', 'Workspace invitation created. Email delivery will depend on the configured mail server.');
    }

    public function resendInvitation(Request $request, Workspace $workspace, WorkspaceInvitation $invitation)
    {
        $this->authorizeInvitationBelongsToWorkspace($workspace, $invitation);

        $actor = $request->user();
        $actorRole = $this->requireMemberPageAccess($actor, $workspace);

        if (! $this->canInviteMembers($actorRole)) {
            abort(403, 'You cannot resend workspace invitations.');
        }

        if ($invitation->status !== 'pending') {
            return back()->withErrors(['invitation' => 'Only pending invitations can be resent.']);
        }

        $invitation->update(['expires_at' => now()->addDays(14)]);
        $invitation = $invitation->fresh(['workspace', 'inviter']);

        AuditLogger::workspaceInvitationResent($invitation, ['source' => 'portal']);
        app(NotificationService::class)->notifyWorkspaceInvitationSent($invitation, $actor);

        return redirect()
            ->route('workspace.members.index', $workspace)
            ->with('success', 'Invitation resent.');
    }

    public function revokeInvitation(Request $request, Workspace $workspace, WorkspaceInvitation $invitation)
    {
        $this->authorizeInvitationBelongsToWorkspace($workspace, $invitation);

        $actorRole = $this->requireMemberPageAccess($request->user(), $workspace);

        if (! $this->canInviteMembers($actorRole)) {
            abort(403, 'You cannot revoke workspace invitations.');
        }

        if ($invitation->status === 'pending') {
            $invitation->update(['status' => 'revoked']);
            AuditLogger::workspaceInvitationRevoked($invitation->fresh(), ['source' => 'portal']);
        }

        return redirect()
            ->route('workspace.members.index', $workspace)
            ->with('success', 'Invitation revoked.');
    }

    public function showInvitation(string $token)
    {
        $invitation = WorkspaceInvitation::with('workspace')
            ->where('token', $token)
            ->firstOrFail();

        $invitation->markExpiredIfNeeded();
        $invitation->refresh();

        return view('workspace.invitations.show', compact('invitation'));
    }

    public function acceptInvitation(Request $request, string $token)
    {
        $invitation = WorkspaceInvitation::with('workspace')
            ->where('token', $token)
            ->firstOrFail();

        $invitation->markExpiredIfNeeded();
        $invitation->refresh();

        if (! $invitation->isPending()) {
            return redirect()
                ->route('workspace.invitations.show', $invitation->token)
                ->withErrors(['invitation' => 'This invitation is no longer pending.']);
        }

        $user = $request->user();

        if (strtolower($user->email) !== strtolower($invitation->email)) {
            return redirect()
                ->route('workspace.invitations.show', $invitation->token)
                ->withErrors(['invitation' => 'Sign in with the invited email address to accept this invitation.']);
        }

        $member = DB::transaction(function () use ($invitation, $user) {
            $member = $invitation->workspace->members()->where('user_id', $user->id)->first();

            if ($member) {
                $member->update([
                    'role' => $invitation->workspace_role,
                    'status' => 'active',
                    'joined_at' => $member->joined_at ?? now(),
                    'removed_at' => null,
                ]);
            } else {
                $member = WorkspaceMember::create([
                    'workspace_id' => $invitation->workspace_id,
                    'user_id' => $user->id,
                    'role' => $invitation->workspace_role,
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            }

            $invitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'accepted_by' => $user->id,
            ]);

            return $member->fresh(['workspace', 'user']);
        });

        $invitation = $invitation->fresh(['workspace', 'inviter', 'acceptedBy']);
        AuditLogger::workspaceInvitationAccepted($invitation);
        AuditLogger::workspaceMembershipAdded($invitation->workspace, $member, ['source' => 'invitation']);
        app(NotificationService::class)->notifyWorkspaceMemberAdded($member, $user);
        app(NotificationService::class)->notifyWorkspaceInvitationAccepted($invitation, $user);

        return redirect()
            ->route('workspace.show', $invitation->workspace)
            ->with('success', 'Workspace invitation accepted.');
    }

    private function requireMemberPageAccess(User $user, Workspace $workspace): string
    {
        $role = $workspace->resolveUserWorkspaceRole($user);

        if (! in_array($role, ['admin', 'workspace_admin', 'manager', 'client_admin', 'client_staff', 'talent', 'assigned_user'], true)) {
            abort(403, 'You cannot view workspace members.');
        }

        return $role;
    }

    private function canManageMembers(string $role): bool
    {
        return in_array($role, ['admin', 'workspace_admin', 'client_admin'], true);
    }

    private function canInviteMembers(string $role): bool
    {
        return $this->canManageMembers($role);
    }

    private function allowedRolesForActor(string $role): array
    {
        return match ($role) {
            'admin', 'workspace_admin' => self::WORKSPACE_ROLES,
            'client_admin' => ['client_staff'],
            default => [],
        };
    }

    private function ensureTargetAllowed(
        User $actor,
        string $actorRole,
        Workspace $workspace,
        User $target,
        string $workspaceRole
    ): void {
        if ($actorRole !== 'admin' && $target->hasAnyRole(['super_admin', 'operations_admin'])) {
            throw ValidationException::withMessages([
                'user_id' => 'Only system admins can add system admin users to a workspace.',
            ]);
        }

        if ($actorRole === 'client_admin' && $workspaceRole !== 'client_staff') {
            throw ValidationException::withMessages([
                'workspace_role' => 'Client admins can add or invite client staff only.',
            ]);
        }

        if ($workspaceRole === 'talent' && ! ($target->hasRole('talent') || $target->talentProfile)) {
            throw ValidationException::withMessages([
                'user_id' => 'Talent workspace members must have a talent platform role or profile.',
            ]);
        }

        if ($workspaceRole === 'manager' && ! ($target->hasRole('line_manager') || $target->managerProfile)) {
            throw ValidationException::withMessages([
                'user_id' => 'Manager workspace members must have a line manager platform role or profile.',
            ]);
        }

        if (in_array($workspaceRole, ['client_admin', 'client_staff'], true)) {
            $hasClientRole = $target->hasAnyRole(['individual_client', 'business_client_admin', 'business_client_staff']);

            if (! $hasClientRole && ! $target->clientProfile) {
                throw ValidationException::withMessages([
                    'user_id' => 'Client workspace members must have a client platform role or profile.',
                ]);
            }

            if ($actorRole === 'client_admin') {
                $this->ensureClientCompanyMatch($workspace, $target);
            }
        }
    }

    private function ensureInviteEmailAllowed(string $actorRole, Workspace $workspace, string $email, string $workspaceRole): void
    {
        if ($actorRole !== 'client_admin') {
            return;
        }

        if ($workspaceRole !== 'client_staff') {
            throw ValidationException::withMessages([
                'workspace_role' => 'Client admins can invite client staff only.',
            ]);
        }

        $domain = $workspace->company?->company_email_domain;

        if ($domain && ! str_ends_with(strtolower($email), '@' . strtolower($domain))) {
            throw ValidationException::withMessages([
                'email' => 'Client staff invitations must use the company email domain.',
            ]);
        }
    }

    private function ensureClientCompanyMatch(Workspace $workspace, User $target): void
    {
        $workspaceCompanyId = $workspace->company_id ?? $workspace->clientProfile?->company_id;

        if (! $workspaceCompanyId) {
            return;
        }

        $targetCompanyId = $target->clientProfile?->company_id;

        if ($targetCompanyId && (int) $targetCompanyId !== (int) $workspaceCompanyId) {
            throw ValidationException::withMessages([
                'user_id' => 'Client staff must belong to the same client company.',
            ]);
        }
    }

    private function authorizeMemberBelongsToWorkspace(Workspace $workspace, WorkspaceMember $member): void
    {
        if ((int) $member->workspace_id !== (int) $workspace->id) {
            abort(404, 'Member not found in this workspace.');
        }
    }

    private function authorizeInvitationBelongsToWorkspace(Workspace $workspace, WorkspaceInvitation $invitation): void
    {
        if ((int) $invitation->workspace_id !== (int) $workspace->id) {
            abort(404, 'Invitation not found in this workspace.');
        }
    }
}
