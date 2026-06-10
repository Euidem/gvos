<?php

namespace App\Http\Controllers;

use App\Notifications\GvosNotification;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public const TIMEZONES = [
        'Africa/Lagos',
        'UTC',
        'Europe/London',
        'Europe/Paris',
        'Europe/Berlin',
        'America/New_York',
        'America/Chicago',
        'America/Denver',
        'America/Los_Angeles',
        'America/Toronto',
        'America/Vancouver',
    ];

    /**
     * Show the onboarding page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Ensure profile row exists
        $user->profile()->firstOrCreate(['user_id' => $user->id]);
        $user->load('profile');
        $user->load(['talentProfile', 'managerProfile', 'clientProfile']);

        $profile    = $user->profile;
        $workspace  = $user->primaryWorkspace();
        $checklist  = $user->onboardingChecklist();
        $percentage = $user->onboardingCompletionPercentage();
        $roleLabels = [
            'talent'                => 'Talent',
            'line_manager'          => 'Manager',
            'individual_client'     => 'Client',
            'business_client_admin' => 'Business Client',
            'business_client_staff' => 'Client Staff',
            'active_lead'           => 'Active Lead',
            'super_admin'           => 'Admin',
            'operations_admin'      => 'Operations Admin',
        ];
        $roleLabel = $roleLabels[$user->getGvosRoleName()] ?? ucwords(str_replace('_', ' ', $user->getGvosRoleName()));
        $timezones = self::TIMEZONES;

        // Mark onboarding as in_progress if still pending
        if ($profile && $profile->onboarding_status === 'pending') {
            $profile->update([
                'onboarding_status'   => 'in_progress',
                'last_onboarding_step' => 'started',
            ]);
        }

        return view('onboarding.index', compact(
            'user',
            'profile',
            'workspace',
            'checklist',
            'percentage',
            'roleLabel',
            'timezones'
        ));
    }

    /**
     * Update profile fields from the onboarding form.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'timezone'   => ['nullable', 'string', Rule::in(self::TIMEZONES)],
            'country'    => ['nullable', 'string', 'max:100'],
            'city'       => ['nullable', 'string', 'max:100'],
            'bio'        => ['nullable', 'string', 'max:500'],
        ]);

        // Update timezone on user record
        if (! empty($validated['timezone'])) {
            $user->update(['timezone' => $validated['timezone']]);
        }

        // Update display name if name is currently the email or placeholder
        $currentName = $user->name ?? '';
        if (str_contains($currentName, '@') || $currentName === trim($validated['first_name'] . ' ' . $validated['last_name'])) {
            $user->update(['name' => trim($validated['first_name'] . ' ' . $validated['last_name'])]);
        }

        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        $isNowComplete = filled($validated['first_name']) && filled($validated['last_name']);
        $wasAlreadyComplete = $profile->onboarding_status === 'complete';

        $profileData = [
            'first_name'          => $validated['first_name'],
            'last_name'           => $validated['last_name'],
            'phone'               => $validated['phone'] ?? $profile->phone,
            'country'             => $validated['country'] ?? $profile->country,
            'city'                => $validated['city'] ?? $profile->city,
            'bio'                 => $validated['bio'] ?? $profile->bio,
            'last_onboarding_step' => 'profile',
        ];

        // Auto-complete onboarding when required fields are done and workspace exists
        if ($isNowComplete && ! $wasAlreadyComplete) {
            $profileData['onboarding_status']       = 'in_progress'; // completeStep finalizes
            $profileData['last_onboarding_step']    = 'profile';
        }

        $profile->update($profileData);

        AuditLogger::onboardingProfileUpdated($user, [
            'fields_updated' => array_keys(array_filter($validated)),
        ]);

        return redirect()
            ->route('onboarding.index')
            ->with('status', 'profile-saved');
    }

    /**
     * Mark onboarding as complete (explicit user action).
     */
    public function completeStep(Request $request): RedirectResponse
    {
        $user    = $request->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        if ($profile->onboarding_status !== 'complete') {
            $profile->update([
                'onboarding_status'      => 'complete',
                'onboarding_completed_at' => now(),
                'last_onboarding_step'   => 'complete',
            ]);

            AuditLogger::onboardingCompleted($user);

            // Notify the user — gentle congratulations
            $this->notifyOnboardingComplete($user);
        }

        // Redirect to primary workspace or dashboard
        $workspace = $user->primaryWorkspace();
        if ($workspace) {
            return redirect()
                ->route('workspace.show', $workspace)
                ->with('success', 'Setup complete! Welcome to your workspace.');
        }

        return redirect($user->getDashboardRoute())
            ->with('success', 'Profile setup complete. Welcome to GVOS!');
    }

    /**
     * Send a congratulatory database notification on onboarding completion.
     * Fails silently if notification dispatch errors occur.
     */
    private function notifyOnboardingComplete($user): void
    {
        try {
            $workspace = $user->primaryWorkspace();
            $notification = new class('onboarding_completed', [
                'title'      => 'Setup complete',
                'message'    => 'You have completed your profile setup. Welcome to GVOS!',
                'action_url' => $workspace ? route('workspace.show', $workspace) : $user->getDashboardRoute(),
                'level'      => 'success',
            ]) extends GvosNotification {};

            $user->notify($notification->forChannels(['database']));

            // Notify workspace manager/admin that talent completed onboarding
            if ($workspace && $user->hasRole('talent')) {
                app(NotificationService::class)->notifyWorkspaceMemberAdded(
                    $workspace->activeMembers()->where('user_id', $user->id)->first() ?? new \App\Models\WorkspaceMember(),
                    $user
                );
            }
        } catch (\Throwable) {
            // Non-critical — never block the redirect
        }
    }
}
