<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\ManagerProfile;
use App\Models\TalentProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class WorkspaceInvitationController extends Controller
{
    /**
     * Show the invitation review page.
     *
     * Public route — detects whether the invited email already has a GVOS account
     * so the view can render the correct action (accept, login, or register).
     */
    public function show(Request $request, string $token): mixed
    {
        $invitation = WorkspaceInvitation::with(['workspace', 'inviter'])
            ->where('token', $token)
            ->firstOrFail();

        $invitation->markExpiredIfNeeded();
        $invitation->refresh();

        $emailHasAccount      = User::where('email', strtolower($invitation->email))->exists();
        $loggedInUser         = $request->user();
        $emailMatchesLoggedIn = $loggedInUser
            && strtolower($loggedInUser->email) === strtolower($invitation->email);

        return view('workspace.invitations.show', compact(
            'invitation',
            'emailHasAccount',
            'emailMatchesLoggedIn',
            'loggedInUser'
        ));
    }

    /**
     * Accept an invitation for an already-authenticated user.
     *
     * Requires auth. Verifies the logged-in user's email matches the invitation email.
     */
    public function accept(Request $request, string $token): mixed
    {
        $invitation = WorkspaceInvitation::with('workspace')
            ->where('token', $token)
            ->firstOrFail();

        $invitation->markExpiredIfNeeded();
        $invitation->refresh();

        if (! $invitation->isPending()) {
            return redirect()
                ->route('workspace.invitations.show', $token)
                ->withErrors(['invitation' => 'This invitation is no longer pending.']);
        }

        $user = $request->user();

        if (strtolower($user->email) !== strtolower($invitation->email)) {
            return redirect()
                ->route('workspace.invitations.show', $token)
                ->withErrors(['invitation' => 'You are signed in as ' . $user->email . '. Please sign in with ' . $invitation->email . ' to accept this invitation.']);
        }

        $member = DB::transaction(function () use ($invitation, $user) {
            $existing = $invitation->workspace->members()->where('user_id', $user->id)->first();

            if ($existing) {
                $existing->update([
                    'role'       => $invitation->workspace_role,
                    'status'     => 'active',
                    'joined_at'  => $existing->joined_at ?? now(),
                    'removed_at' => null,
                ]);
                $member = $existing->fresh(['workspace', 'user']);
            } else {
                $member = WorkspaceMember::create([
                    'workspace_id' => $invitation->workspace_id,
                    'user_id'      => $user->id,
                    'role'         => $invitation->workspace_role,
                    'status'       => 'active',
                    'joined_at'    => now(),
                ])->fresh(['workspace', 'user']);
            }

            $invitation->update([
                'status'      => 'accepted',
                'accepted_at' => now(),
                'accepted_by' => $user->id,
            ]);

            return $member;
        });

        $invitation = $invitation->fresh(['workspace', 'inviter', 'acceptedBy']);

        AuditLogger::workspaceInvitationAccepted($invitation);
        AuditLogger::workspaceMembershipAdded($invitation->workspace, $member, ['source' => 'invitation']);
        app(NotificationService::class)->notifyWorkspaceMemberAdded($member, $user);
        app(NotificationService::class)->notifyWorkspaceInvitationAccepted($invitation, $user);

        // Send users with incomplete onboarding to the onboarding page first
        $user->load('profile');
        if ($user->needsOnboarding()) {
            return redirect()
                ->route('onboarding.index')
                ->with('success', 'You have joined ' . ($invitation->workspace->name ?? 'the workspace') . '. Complete your profile to get started.');
        }

        return redirect()
            ->route('workspace.show', $invitation->workspace)
            ->with('success', 'Welcome to ' . ($invitation->workspace->name ?? 'the workspace') . '!');
    }

    /**
     * Register a new GVOS account from an invitation link and immediately accept.
     *
     * Public route — for invited emails with no existing account.
     * Creates user, profile stub, workspace membership, and logs them in.
     * Wrapped in a database transaction.
     * Token and password are never logged.
     */
    public function registerAndAccept(Request $request, string $token): mixed
    {
        $invitation = WorkspaceInvitation::with('workspace')
            ->where('token', $token)
            ->firstOrFail();

        $invitation->markExpiredIfNeeded();
        $invitation->refresh();

        if (! $invitation->isPending()) {
            return redirect()
                ->route('workspace.invitations.show', $token)
                ->withErrors(['invitation' => 'This invitation is no longer pending.']);
        }

        // Redirect already-logged-in users to the accept flow
        if ($request->user()) {
            return redirect()->route('workspace.invitations.show', $token);
        }

        $email = strtolower($invitation->email);

        // Block if account already exists — user should log in instead
        if (User::where('email', $email)->exists()) {
            return redirect()
                ->route('workspace.invitations.show', $token)
                ->withErrors(['invitation' => 'An account already exists for this email. Please sign in to accept your invitation.']);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'password'   => ['required', 'confirmed', Password::min(8)],
            'phone'      => ['nullable', 'string', 'max:30'],
            'timezone'   => ['nullable', 'string', 'max:100'],
        ]);

        $platformRole = $this->resolveSafePlatformRole($invitation);

        $member = DB::transaction(function () use ($invitation, $validated, $email, $platformRole) {
            $firstName   = $validated['first_name'];
            $lastName    = $validated['last_name'];
            $displayName = trim($firstName . ' ' . $lastName) ?: $email;

            $user = User::create([
                'name'     => $displayName,
                'email'    => $email,
                'password' => $validated['password'], // hashed by the model's hashed cast
                'timezone' => $validated['timezone'] ?? 'Africa/Lagos',
                'status'   => 'active',
            ]);

            UserProfile::create([
                'user_id'           => $user->id,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'phone'             => $validated['phone'] ?? null,
                'onboarding_status' => 'pending',
            ]);

            $user->assignRole($platformRole);

            $this->createProfileStub($user, $platformRole);

            $existing = $invitation->workspace->members()->where('user_id', $user->id)->first();

            if ($existing) {
                $existing->update([
                    'role'       => $invitation->workspace_role,
                    'status'     => 'active',
                    'joined_at'  => $existing->joined_at ?? now(),
                    'removed_at' => null,
                ]);
                $member = $existing->fresh(['workspace', 'user']);
            } else {
                $member = WorkspaceMember::create([
                    'workspace_id' => $invitation->workspace_id,
                    'user_id'      => $user->id,
                    'role'         => $invitation->workspace_role,
                    'status'       => 'active',
                    'joined_at'    => now(),
                ])->fresh(['workspace', 'user']);
            }

            $invitation->update([
                'status'      => 'accepted',
                'accepted_at' => now(),
                'accepted_by' => $user->id,
            ]);

            return $member;
        });

        $user       = $member->user;
        $invitation = $invitation->fresh(['workspace', 'inviter', 'acceptedBy']);

        // Audit — no token or password in any log entry
        AuditLogger::userCreated($user, [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'role'       => $platformRole,
            'source'     => 'invitation_registration',
        ]);
        AuditLogger::workspaceInvitationRegisteredAndAccepted($invitation);
        AuditLogger::workspaceMembershipAdded($invitation->workspace, $member, ['source' => 'invitation_registration']);

        app(NotificationService::class)->notifyWorkspaceMemberAdded($member, $user);
        app(NotificationService::class)->notifyWorkspaceInvitationAccepted($invitation, $user);

        Auth::login($user);

        // New users are sent to onboarding to complete their profile first.
        // The workspace they joined is visible on the onboarding page.
        return redirect()
            ->route('onboarding.index')
            ->with('success', 'Welcome to GVOS! Your account has been created and you have been added to ' . ($invitation->workspace->name ?? 'the workspace') . '. Complete your profile to get started.');
    }

    /**
     * Determine the safest platform role to assign to a new user.
     *
     * Uses the invitation's platform_role if set and safe, otherwise infers from workspace_role.
     * Never assigns super_admin or operations_admin via invitation.
     */
    private function resolveSafePlatformRole(WorkspaceInvitation $invitation): string
    {
        $forbidden = ['super_admin', 'operations_admin'];

        if ($invitation->platform_role && ! in_array($invitation->platform_role, $forbidden, true)) {
            $allowed = array_keys(WorkspaceInvitation::platformRoleLabels());
            if (in_array($invitation->platform_role, $allowed, true)) {
                return $invitation->platform_role;
            }
        }

        return match ($invitation->workspace_role) {
            'talent'          => 'talent',
            'manager'         => 'line_manager',
            'workspace_admin' => 'line_manager',
            'client_admin'    => 'business_client_admin',
            'client_staff'    => 'business_client_staff',
            default           => 'individual_client',
        };
    }

    /**
     * Create a role-appropriate profile stub, matching the pattern used in Filament CreateUser.
     */
    private function createProfileStub(User $user, string $platformRole): void
    {
        match ($platformRole) {
            'talent' => TalentProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['status' => 'pending', 'training_status' => 'not_started', 'equipment_status' => 'not_assigned']
            ),
            'line_manager' => ManagerProfile::firstOrCreate(
                ['user_id' => $user->id],
                ['status' => 'pending', 'capacity_limit' => 10, 'current_load' => 0]
            ),
            'individual_client',
            'business_client_admin',
            'business_client_staff' => ClientProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'client_type' => match ($platformRole) {
                        'business_client_admin' => 'business_admin',
                        'business_client_staff' => 'business_staff',
                        default                 => 'individual',
                    },
                    'status' => 'pending',
                ]
            ),
            default => null,
        };
    }
}
