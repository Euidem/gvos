<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Allowed timezone values — kept in sync with the Blade dropdown
     * and UserResource::timezoneOptions().
     */
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
     * Show the profile editing page.
     * Ensures a UserProfile record exists for the user.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        // Ensure a profile row always exists
        $user->profile()->firstOrCreate(['user_id' => $user->id]);
        $user->load('profile');

        return view('profile.edit', [
            'user'    => $user,
            'profile' => $user->profile,
        ]);
    }

    /**
     * Update user display name, timezone and extended profile fields.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'timezone'   => ['required', 'string', Rule::in(self::TIMEZONES)],
            'first_name' => ['nullable', 'string', 'max:100'],
            'last_name'  => ['nullable', 'string', 'max:100'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'country'    => ['nullable', 'string', 'max:100'],
            'city'       => ['nullable', 'string', 'max:100'],
            'bio'        => ['nullable', 'string', 'max:500'],
        ]);

        // Snapshot for audit log
        $changes = [];
        foreach (['name', 'email', 'timezone'] as $field) {
            if (isset($validated[$field]) && $user->$field !== $validated[$field]) {
                $changes[$field] = ['from' => $user->$field, 'to' => $validated[$field]];
            }
        }

        $user->update([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'timezone' => $validated['timezone'],
        ]);

        // Update or create extended profile
        $profileData = [
            'first_name' => $validated['first_name'] ?? null,
            'last_name'  => $validated['last_name']  ?? null,
            'phone'      => $validated['phone']       ?? null,
            'country'    => $validated['country']     ?? null,
            'city'       => $validated['city']        ?? null,
            'bio'        => $validated['bio']         ?? null,
        ];

        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        // Mark onboarding complete once first + last name are filled in
        $isProfileFilled = filled($validated['first_name']) && filled($validated['last_name']);
        if ($isProfileFilled && $profile->onboarding_status === 'pending') {
            $profileData['onboarding_status'] = 'complete';
        }

        $profile->update($profileData);

        AuditLogger::profileUpdated($user, $changes);

        return redirect()->route('profile.show')
                         ->with('status', 'profile-updated');
    }
}
