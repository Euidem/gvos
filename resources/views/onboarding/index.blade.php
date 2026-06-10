<x-layouts.gvos title="Complete Your Setup">
{{-- Phase 16 Onboarding — GVOS design system --}}
@php
    $firstName    = $profile?->first_name ?? '';
    $welcomeName  = $firstName ?: $user->name ?? 'there';
    $isComplete   = ($profile?->onboarding_status === 'complete');
    $requiredDone = $user->hasCompletedRequiredProfile();
@endphp

{{-- Success / status flash --}}
@if (session('status') === 'profile-saved')
    <div class="mb-6 flex items-center gap-3 rounded-lg border border-status-active/20 bg-status-active/10 px-4 py-3 text-status-active text-sm font-medium">
        <span class="material-symbols-outlined" style="font-size:18px">check_circle</span>
        Profile saved successfully.
    </div>
@endif
@if (session('success'))
    <div class="mb-6 flex items-center gap-3 rounded-lg border border-status-active/20 bg-status-active/10 px-4 py-3 text-status-active text-sm font-medium">
        <span class="material-symbols-outlined" style="font-size:18px">check_circle</span>
        {{ session('success') }}
    </div>
@endif

{{-- ── Page header ─────────────────────────────────────────────────────── --}}
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="font-headline-lg text-headline-lg text-primary">
            {{ $isComplete ? 'Your Setup' : 'Complete Your Setup' }}
        </h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Welcome, {{ $welcomeName }} — you are logged in as <strong>{{ $roleLabel }}</strong>.
        </p>
    </div>
    @if (! $isComplete)
        <a href="{{ $user->getDashboardRoute() }}"
           class="text-sm text-outline hover:text-secondary transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined" style="font-size:16px">skip_next</span>
            I'll finish this later
        </a>
    @else
        <a href="{{ $workspace ? route('workspace.show', $workspace) : $user->getDashboardRoute() }}"
           class="inline-flex items-center gap-2 bg-secondary text-on-secondary px-4 py-2 rounded-lg
                  font-label-md text-label-md hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" style="font-size:16px">arrow_forward</span>
            Go to {{ $workspace ? $workspace->name : 'Dashboard' }}
        </a>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl">

    {{-- ── Left column: progress + checklist ────────────────────────────── --}}
    <div class="lg:col-span-1 space-y-6">

        {{-- Progress card --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
            <h2 class="font-headline-md text-headline-md text-primary mb-4">Setup Progress</h2>

            {{-- Percentage ring (CSS-only) --}}
            <div class="flex items-center gap-4 mb-4">
                <div class="relative w-16 h-16 flex-shrink-0">
                    <svg class="w-16 h-16 -rotate-90" viewBox="0 0 64 64">
                        <circle cx="32" cy="32" r="26" fill="none" stroke="#E2E8F0" stroke-width="6"/>
                        <circle cx="32" cy="32" r="26" fill="none" stroke="#0058be" stroke-width="6"
                            stroke-dasharray="{{ round(2 * 3.14159 * 26 * $percentage / 100, 1) }} {{ round(2 * 3.14159 * 26, 1) }}"
                            stroke-linecap="round"/>
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center font-bold text-sm text-secondary">
                        {{ $percentage }}%
                    </span>
                </div>
                <div>
                    <p class="font-body-md text-body-md text-on-surface font-semibold">
                        @if ($percentage === 100)
                            All set!
                        @elseif ($percentage >= 50)
                            Almost there
                        @else
                            Getting started
                        @endif
                    </p>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        {{ $percentage }}% of required steps complete
                    </p>
                </div>
            </div>

            {{-- Checklist --}}
            <ul class="space-y-3">
                @foreach ($checklist as $item)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex-shrink-0 material-symbols-outlined text-{{ $item['done'] ? 'status-active' : 'outline' }}"
                              style="font-size:18px; {{ $item['done'] ? 'color:#10B981' : 'color:#76777d' }}">
                            {{ $item['done'] ? 'check_circle' : 'radio_button_unchecked' }}
                        </span>
                        <span class="font-body-sm text-body-sm {{ $item['done'] ? 'text-on-surface line-through opacity-60' : 'text-on-surface' }}">
                            {{ $item['label'] }}
                            @if (! empty($item['optional']))
                                <span class="text-outline ml-1">(optional)</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>

            {{-- Mark complete button --}}
            @if (! $isComplete && $requiredDone)
                <form method="POST" action="{{ route('onboarding.step.complete') }}" class="mt-6">
                    @csrf
                    <button type="submit"
                            class="w-full bg-secondary text-on-secondary py-2.5 rounded-lg font-label-md text-label-md
                                   hover:brightness-110 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:18px">task_alt</span>
                        Mark Setup Complete
                    </button>
                </form>
            @elseif ($isComplete)
                <div class="mt-6 flex items-center gap-2 text-status-active text-sm font-medium">
                    <span class="material-symbols-outlined" style="font-size:18px; color:#10B981">verified</span>
                    Setup complete — {{ $profile?->onboarding_completed_at?->format('d M Y') ?? 'completed' }}
                </div>
            @endif
        </div>

        {{-- Workspace card --}}
        @if ($workspace)
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
                <h2 class="font-headline-md text-headline-md text-primary mb-3">Your Workspace</h2>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-secondary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-secondary" style="font-size:20px">workspaces</span>
                    </div>
                    <div>
                        <p class="font-body-md text-body-md text-on-surface font-semibold">{{ $workspace->name }}</p>
                        <p class="font-body-sm text-body-sm text-on-surface-variant capitalize">
                            {{ str_replace('_', ' ', $workspace->status) }}
                        </p>
                    </div>
                </div>
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="w-full flex items-center justify-center gap-2 border border-secondary text-secondary
                          py-2 rounded-lg font-label-md text-label-md hover:bg-secondary/5 transition-colors">
                    <span class="material-symbols-outlined" style="font-size:16px">open_in_new</span>
                    Open Workspace
                </a>
            </div>
        @else
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
                <h2 class="font-headline-md text-headline-md text-primary mb-2">Your Workspace</h2>
                <div class="flex flex-col items-center justify-center py-4 text-center">
                    <span class="material-symbols-outlined text-outline mb-2" style="font-size:36px">workspaces</span>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">
                        You have not been assigned to a workspace yet.<br>
                        Your manager or admin will add you soon.
                    </p>
                </div>
            </div>
        @endif

    </div>

    {{-- ── Right column: profile form ────────────────────────────────────── --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6 lg:p-8">
            <h2 class="font-headline-md text-headline-md text-primary mb-1">Your Profile</h2>
            <p class="font-body-sm text-body-sm text-on-surface-variant mb-6">
                Fill in the required fields to complete your setup. You can update these any time from
                <a href="{{ route('profile.show') }}" class="text-secondary hover:underline">Settings → Profile</a>.
            </p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-status-blocked/20 bg-status-blocked/10 p-4 text-sm text-status-blocked">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('onboarding.profile.update') }}" class="space-y-5">
                @csrf

                {{-- Name row --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wide mb-1.5">
                            First Name <span class="text-status-blocked">*</span>
                        </label>
                        <input type="text" name="first_name"
                               value="{{ old('first_name', $profile?->first_name) }}"
                               class="w-full border border-border-subtle rounded-lg px-3 py-2.5 font-body-md text-body-md
                                      text-on-surface bg-surface-container-low focus:outline-none focus:ring-2
                                      focus:ring-secondary/20 focus:border-secondary transition-colors"
                               placeholder="Your first name" required>
                        @error('first_name')
                            <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wide mb-1.5">
                            Last Name <span class="text-status-blocked">*</span>
                        </label>
                        <input type="text" name="last_name"
                               value="{{ old('last_name', $profile?->last_name) }}"
                               class="w-full border border-border-subtle rounded-lg px-3 py-2.5 font-body-md text-body-md
                                      text-on-surface bg-surface-container-low focus:outline-none focus:ring-2
                                      focus:ring-secondary/20 focus:border-secondary transition-colors"
                               placeholder="Your last name" required>
                        @error('last_name')
                            <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Timezone --}}
                <div>
                    <label class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wide mb-1.5">
                        Timezone
                    </label>
                    <select name="timezone"
                            class="w-full border border-border-subtle rounded-lg px-3 py-2.5 font-body-md text-body-md
                                   text-on-surface bg-surface-container-low focus:outline-none focus:ring-2
                                   focus:ring-secondary/20 focus:border-secondary transition-colors">
                        @foreach ($timezones as $tz)
                            <option value="{{ $tz }}" @selected(old('timezone', $user->timezone) === $tz)>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Phone + Country --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wide mb-1.5">
                            Phone <span class="text-outline">(optional)</span>
                        </label>
                        <input type="tel" name="phone"
                               value="{{ old('phone', $profile?->phone) }}"
                               class="w-full border border-border-subtle rounded-lg px-3 py-2.5 font-body-md text-body-md
                                      text-on-surface bg-surface-container-low focus:outline-none focus:ring-2
                                      focus:ring-secondary/20 focus:border-secondary transition-colors"
                               placeholder="+1 555 000 0000">
                    </div>
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wide mb-1.5">
                            Country <span class="text-outline">(optional)</span>
                        </label>
                        <input type="text" name="country"
                               value="{{ old('country', $profile?->country) }}"
                               class="w-full border border-border-subtle rounded-lg px-3 py-2.5 font-body-md text-body-md
                                      text-on-surface bg-surface-container-low focus:outline-none focus:ring-2
                                      focus:ring-secondary/20 focus:border-secondary transition-colors"
                               placeholder="Your country" maxlength="100">
                    </div>
                </div>

                {{-- City + Bio --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wide mb-1.5">
                            City <span class="text-outline">(optional)</span>
                        </label>
                        <input type="text" name="city"
                               value="{{ old('city', $profile?->city) }}"
                               class="w-full border border-border-subtle rounded-lg px-3 py-2.5 font-body-md text-body-md
                                      text-on-surface bg-surface-container-low focus:outline-none focus:ring-2
                                      focus:ring-secondary/20 focus:border-secondary transition-colors"
                               placeholder="Your city" maxlength="100">
                    </div>
                    <div class="sm:col-span-1">
                        {{-- Role-specific tip --}}
                        <div class="rounded-lg bg-secondary/5 border border-secondary/20 p-4 h-full flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size:18px">info</span>
                            <p class="font-body-sm text-body-sm text-secondary/80">
                                @if ($user->hasRole('talent'))
                                    As a talent, completing your profile helps your manager understand your role and working preferences.
                                @elseif ($user->hasRole('line_manager'))
                                    As a manager, your profile is visible to your workspace team.
                                @elseif ($user->hasAnyRole(['individual_client','business_client_admin']))
                                    Your profile is used for billing and workspace communication.
                                @else
                                    Your profile helps your workspace admin identify you.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block font-label-md text-label-md text-on-surface-variant uppercase tracking-wide mb-1.5">
                        Short Bio <span class="text-outline">(optional)</span>
                    </label>
                    <textarea name="bio" rows="3"
                              class="w-full border border-border-subtle rounded-lg px-3 py-2.5 font-body-md text-body-md
                                     text-on-surface bg-surface-container-low focus:outline-none focus:ring-2
                                     focus:ring-secondary/20 focus:border-secondary transition-colors resize-none"
                              placeholder="A brief summary about yourself or your role..." maxlength="500">{{ old('bio', $profile?->bio) }}</textarea>
                </div>

                {{-- Submit --}}
                <div class="flex items-center justify-between pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-secondary text-on-secondary px-6 py-2.5 rounded-lg
                                   font-label-md text-label-md hover:brightness-110 active:scale-[0.98] transition-all">
                        <span class="material-symbols-outlined" style="font-size:16px">save</span>
                        Save Profile
                    </button>
                    <a href="{{ $user->getDashboardRoute() }}"
                       class="text-sm text-outline hover:text-secondary transition-colors">
                        Go to Dashboard
                    </a>
                </div>
            </form>
        </div>

        {{-- ── Primary action card based on role ─────────────────────────── --}}
        @if ($workspace && $requiredDone)
            <div class="mt-6 bg-white rounded-xl border border-border-subtle shadow-card p-6">
                <h2 class="font-headline-md text-headline-md text-primary mb-4">Your Next Steps</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @if ($user->hasRole('talent'))
                        <a href="{{ route('workspace.tasks.index', $workspace) }}"
                           class="flex items-center gap-3 p-4 rounded-lg border border-border-subtle hover:border-secondary/30 hover:bg-secondary/5 transition-colors">
                            <span class="material-symbols-outlined text-secondary" style="font-size:22px">task_alt</span>
                            <div>
                                <p class="font-body-md text-body-md text-on-surface font-semibold">View Tasks</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Check your assigned work</p>
                            </div>
                        </a>
                        <a href="{{ route('workspace.time-logs.index', $workspace) }}"
                           class="flex items-center gap-3 p-4 rounded-lg border border-border-subtle hover:border-secondary/30 hover:bg-secondary/5 transition-colors">
                            <span class="material-symbols-outlined text-secondary" style="font-size:22px">schedule</span>
                            <div>
                                <p class="font-body-md text-body-md text-on-surface font-semibold">Log Time</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Start tracking your work</p>
                            </div>
                        </a>
                    @elseif ($user->hasRole('line_manager'))
                        <a href="{{ route('workspace.members.index', $workspace) }}"
                           class="flex items-center gap-3 p-4 rounded-lg border border-border-subtle hover:border-secondary/30 hover:bg-secondary/5 transition-colors">
                            <span class="material-symbols-outlined text-secondary" style="font-size:22px">group</span>
                            <div>
                                <p class="font-body-md text-body-md text-on-surface font-semibold">Manage Team</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Review workspace members</p>
                            </div>
                        </a>
                        <a href="{{ route('workspace.time-logs.index', $workspace) }}"
                           class="flex items-center gap-3 p-4 rounded-lg border border-border-subtle hover:border-secondary/30 hover:bg-secondary/5 transition-colors">
                            <span class="material-symbols-outlined text-secondary" style="font-size:22px">schedule</span>
                            <div>
                                <p class="font-body-md text-body-md text-on-surface font-semibold">Review Time Logs</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Approve submitted sessions</p>
                            </div>
                        </a>
                    @elseif ($user->hasAnyRole(['individual_client','business_client_admin']))
                        <a href="{{ route('workspace.tasks.index', $workspace) }}"
                           class="flex items-center gap-3 p-4 rounded-lg border border-border-subtle hover:border-secondary/30 hover:bg-secondary/5 transition-colors">
                            <span class="material-symbols-outlined text-secondary" style="font-size:22px">task_alt</span>
                            <div>
                                <p class="font-body-md text-body-md text-on-surface font-semibold">View Tasks</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">Track deliverables</p>
                            </div>
                        </a>
                        <a href="{{ route('workspace.billing.index', $workspace) }}"
                           class="flex items-center gap-3 p-4 rounded-lg border border-border-subtle hover:border-secondary/30 hover:bg-secondary/5 transition-colors">
                            <span class="material-symbols-outlined text-secondary" style="font-size:22px">receipt</span>
                            <div>
                                <p class="font-body-md text-body-md text-on-surface font-semibold">Billing</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">View invoices and payments</p>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('workspace.show', $workspace) }}"
                           class="flex items-center gap-3 p-4 rounded-lg border border-border-subtle hover:border-secondary/30 hover:bg-secondary/5 transition-colors sm:col-span-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size:22px">open_in_new</span>
                            <div>
                                <p class="font-body-md text-body-md text-on-surface font-semibold">Open Workspace</p>
                                <p class="font-body-sm text-body-sm text-on-surface-variant">{{ $workspace->name }}</p>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>

</x-layouts.gvos>
