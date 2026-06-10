<x-layouts.public title="Workspace Invitation">
    <div class="max-w-xl mx-auto">
        <div class="bg-white rounded-xl border border-border-subtle shadow-card p-8">

            {{-- Icon + heading --}}
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5" style="background-color:rgba(0,88,190,.08);">
                <span class="material-symbols-outlined text-secondary" style="font-size: 24px;">outgoing_mail</span>
            </div>

            <h1 class="text-2xl font-bold text-on-surface">Workspace Invitation</h1>
            <p class="text-sm text-on-surface-variant mt-2">
                You have been invited to join
                <strong class="text-on-surface">{{ $invitation->workspace?->name ?? 'a GVOS workspace' }}</strong> as
                <strong class="text-on-surface">{{ \App\Models\WorkspaceMember::roleLabels()[$invitation->workspace_role] ?? ucfirst(str_replace('_', ' ', $invitation->workspace_role)) }}</strong>.
            </p>

            {{-- Error messages --}}
            @if ($errors->any())
                <div class="bg-status-blocked/10 border border-status-blocked/20 rounded-xl px-4 py-3 mt-5 text-sm text-status-blocked">
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div class="bg-status-active/10 border border-status-active/20 rounded-xl px-4 py-3 mt-5 text-sm text-status-active">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Invitation detail card --}}
            <div class="mt-6 rounded-xl border border-border-subtle bg-surface-container-low p-4 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-outline">Invited email</span>
                    <span class="font-semibold text-on-surface text-right">{{ $invitation->email }}</span>
                </div>
                @if ($invitation->inviter)
                    <div class="flex justify-between gap-4">
                        <span class="text-outline">Invited by</span>
                        <span class="font-semibold text-on-surface">{{ $invitation->inviter->name }}</span>
                    </div>
                @endif
                <div class="flex justify-between gap-4">
                    <span class="text-outline">Workspace role</span>
                    <span class="font-semibold text-on-surface">
                        {{ \App\Models\WorkspaceMember::roleLabels()[$invitation->workspace_role] ?? ucfirst(str_replace('_', ' ', $invitation->workspace_role)) }}
                    </span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-outline">Status</span>
                    <span class="font-semibold text-on-surface">
                        {{ \App\Models\WorkspaceInvitation::statusLabels()[$invitation->status] ?? ucfirst($invitation->status) }}
                    </span>
                </div>
                @if ($invitation->expires_at)
                    <div class="flex justify-between gap-4">
                        <span class="text-outline">Expires</span>
                        <span class="font-semibold text-on-surface {{ $invitation->isExpired() ? 'text-status-blocked' : '' }}">
                            {{ $invitation->expires_at->format('d M Y H:i') }}
                        </span>
                    </div>
                @endif
            </div>

            {{-- ── Action section ─────────────────────────────────────────── --}}

            @if ($invitation->isPending())

                {{-- Scenario 1: Logged in with matching email — show Accept button --}}
                @if ($loggedInUser && $emailMatchesLoggedIn)
                    <div class="mt-6 p-4 rounded-xl border border-status-active/20 bg-status-active/5 text-sm">
                        <p class="text-status-active font-semibold flex items-center gap-1.5">
                            <span class="material-symbols-outlined" style="font-size:16px;">check_circle</span>
                            Signed in as {{ $loggedInUser->email }}
                        </p>
                        <p class="text-on-surface-variant mt-1">
                            Accept the invitation to join {{ $invitation->workspace?->name }}.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('workspace.invitations.accept', $invitation->token) }}" class="mt-4">
                        @csrf
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg text-sm font-semibold text-white hover:brightness-110 active:scale-[0.98]"
                                style="background-color:#0058be">
                            <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
                            Accept Invitation
                        </button>
                    </form>

                {{-- Scenario 2: Logged in with wrong email — show error and sign-out option --}}
                @elseif ($loggedInUser && ! $emailMatchesLoggedIn)
                    <div class="mt-6 p-4 rounded-xl border border-status-blocked/20 bg-status-blocked/5 text-sm space-y-1">
                        <p class="text-status-blocked font-semibold flex items-center gap-1.5">
                            <span class="material-symbols-outlined" style="font-size:16px;">warning</span>
                            Wrong account
                        </p>
                        <p class="text-on-surface-variant">
                            You are signed in as <strong>{{ $loggedInUser->email }}</strong> but this invitation
                            is for <strong>{{ $invitation->email }}</strong>.
                        </p>
                        <p class="text-on-surface-variant">
                            Sign out and sign in with the invited email to accept, or contact your workspace admin if you need the invitation resent to a different address.
                        </p>
                    </div>
                    <div class="mt-4 space-y-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg text-sm font-semibold border border-border-subtle text-on-surface hover:bg-surface-container-low bg-white active:scale-[0.98]">
                                <span class="material-symbols-outlined" style="font-size: 16px;">logout</span>
                                Sign Out and Use Correct Account
                            </button>
                        </form>
                    </div>

                {{-- Scenario 3: Not logged in but account exists — show Login button --}}
                @elseif (! $loggedInUser && $emailHasAccount)
                    <div class="mt-6 p-4 rounded-xl border border-border-subtle bg-surface-container-low text-sm space-y-1">
                        <p class="text-on-surface font-semibold">Sign in to accept</p>
                        <p class="text-on-surface-variant">
                            A GVOS account already exists for <strong>{{ $invitation->email }}</strong>.
                            Sign in to accept this invitation.
                        </p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('login') }}"
                           class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg text-sm font-semibold text-white hover:brightness-110 active:scale-[0.98]"
                           style="background-color:#0058be">
                            <span class="material-symbols-outlined" style="font-size: 16px;">login</span>
                            Sign In to Accept
                        </a>
                        <p class="text-xs text-outline text-center mt-3">
                            After signing in, return to this link to accept the invitation.
                        </p>
                    </div>

                {{-- Scenario 4: Not logged in and no account — show registration form --}}
                @else
                    <div class="mt-6">
                        <h2 class="text-base font-bold text-on-surface mb-1">Create your GVOS account</h2>
                        <p class="text-xs text-on-surface-variant mb-5">
                            Set up your account to join <strong>{{ $invitation->workspace?->name }}</strong>.
                            Your invitation email is pre-filled and cannot be changed.
                        </p>

                        <form method="POST" action="{{ route('workspace.invitations.register', $invitation->token) }}" class="space-y-4">
                            @csrf

                            {{-- Email locked to invitation --}}
                            <div>
                                <label class="block text-sm font-semibold text-on-surface mb-1">Email</label>
                                <input type="email" value="{{ $invitation->email }}" readonly disabled
                                       class="w-full rounded-lg border border-border-subtle text-sm bg-surface-container-low text-outline px-3 py-2 cursor-not-allowed">
                                <p class="mt-1 text-xs text-outline">This email is locked to your invitation and cannot be changed.</p>
                            </div>

                            {{-- First name + last name --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="first_name" class="block text-sm font-semibold text-on-surface mb-1">First Name</label>
                                    <input type="text" id="first_name" name="first_name"
                                           value="{{ old('first_name') }}" required autocomplete="given-name"
                                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary {{ $errors->has('first_name') ? 'border-status-blocked' : 'border-border-subtle' }}">
                                    @error('first_name')
                                        <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-semibold text-on-surface mb-1">Last Name</label>
                                    <input type="text" id="last_name" name="last_name"
                                           value="{{ old('last_name') }}" required autocomplete="family-name"
                                           class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary {{ $errors->has('last_name') ? 'border-status-blocked' : 'border-border-subtle' }}">
                                    @error('last_name')
                                        <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Password --}}
                            <div>
                                <label for="password" class="block text-sm font-semibold text-on-surface mb-1">Password</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" required autocomplete="new-password"
                                           class="w-full rounded-lg border px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary {{ $errors->has('password') ? 'border-status-blocked' : 'border-border-subtle' }}">
                                    <button type="button" onclick="gvosTogglePwd('password','pwdIcon1')"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-outline hover:text-on-surface" tabindex="-1">
                                        <span id="pwdIcon1" class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-outline">Minimum 8 characters.</p>
                            </div>

                            {{-- Confirm password --}}
                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-on-surface mb-1">Confirm Password</label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                                           class="w-full rounded-lg border border-border-subtle px-3 py-2 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary">
                                    <button type="button" onclick="gvosTogglePwd('password_confirmation','pwdIcon2')"
                                            class="absolute inset-y-0 right-0 flex items-center px-3 text-outline hover:text-on-surface" tabindex="-1">
                                        <span id="pwdIcon2" class="material-symbols-outlined" style="font-size:18px;">visibility</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Phone + Timezone (optional) --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="phone" class="block text-sm font-semibold text-on-surface mb-1">
                                        Phone <span class="text-outline font-normal">(optional)</span>
                                    </label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                                           class="w-full rounded-lg border border-border-subtle px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary">
                                </div>
                                <div>
                                    <label for="timezone" class="block text-sm font-semibold text-on-surface mb-1">
                                        Timezone <span class="text-outline font-normal">(optional)</span>
                                    </label>
                                    <select id="timezone" name="timezone"
                                            class="w-full rounded-lg border border-border-subtle px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary">
                                        <option value="">Select timezone</option>
                                        @foreach ([
                                            'Africa/Lagos'       => 'Africa/Lagos (WAT, UTC+1)',
                                            'Europe/London'      => 'Europe/London (GMT/BST)',
                                            'Europe/Paris'       => 'Europe/Paris (CET, UTC+1)',
                                            'America/New_York'   => 'America/New_York (ET)',
                                            'America/Chicago'    => 'America/Chicago (CT)',
                                            'America/Denver'     => 'America/Denver (MT)',
                                            'America/Los_Angeles'=> 'America/Los_Angeles (PT)',
                                            'America/Toronto'    => 'America/Toronto (ET)',
                                            'Asia/Dubai'         => 'Asia/Dubai (GST, UTC+4)',
                                            'Asia/Kolkata'       => 'Asia/Kolkata (IST, UTC+5:30)',
                                            'Australia/Sydney'   => 'Australia/Sydney (AEST)',
                                        ] as $tz => $label)
                                            <option value="{{ $tz }}" @selected(old('timezone') === $tz)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg text-sm font-semibold text-white hover:brightness-110 active:scale-[0.98] mt-2"
                                    style="background-color:#0058be">
                                <span class="material-symbols-outlined" style="font-size: 16px;">person_add</span>
                                Create Account and Join Workspace
                            </button>
                        </form>

                        <p class="text-xs text-outline text-center mt-5">
                            Already have a GVOS account?
                            <a href="{{ route('login') }}" class="text-secondary hover:underline">Sign in</a>
                        </p>
                    </div>
                @endif

            @else
                {{-- Invitation not pending — show appropriate terminal state --}}
                <div class="mt-6 p-4 rounded-xl border border-border-subtle bg-surface-container-low text-sm space-y-1">
                    <p class="text-on-surface font-semibold">
                        @if ($invitation->status === 'accepted')
                            This invitation has already been accepted.
                        @elseif ($invitation->status === 'revoked')
                            This invitation has been revoked.
                        @elseif ($invitation->status === 'expired')
                            This invitation has expired.
                        @else
                            This invitation is no longer valid.
                        @endif
                    </p>
                    <p class="text-on-surface-variant">
                        Contact your workspace admin if you need a new invitation.
                    </p>
                </div>

                @if ($invitation->status === 'accepted' && $loggedInUser)
                    <div class="mt-4">
                        <a href="{{ route('workspace.show', $invitation->workspace) }}"
                           class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-3 rounded-lg text-sm font-semibold text-white hover:brightness-110 active:scale-[0.98]"
                           style="background-color:#0058be">
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_forward</span>
                            Go to Workspace
                        </a>
                    </div>
                @endif
            @endif

        </div>
    </div>

    <script>
    function gvosTogglePwd(fieldId, iconId) {
        var f = document.getElementById(fieldId);
        var i = document.getElementById(iconId);
        if (f.type === 'password') {
            f.type = 'text';
            i.textContent = 'visibility_off';
        } else {
            f.type = 'password';
            i.textContent = 'visibility';
        }
    }
    </script>
</x-layouts.public>
