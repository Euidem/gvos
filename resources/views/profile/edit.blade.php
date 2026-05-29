<x-layouts.gvos title="My Profile">

    <div class="max-w-2xl mx-auto space-y-8">

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <div>
            <h2 class="text-2xl font-bold text-on-surface">My Profile</h2>
            <p class="text-sm text-on-surface-variant mt-1">Update your personal information and account settings.</p>
        </div>

        {{-- ── Success banners ──────────────────────────────────────────── --}}
        @if (session('status') === 'profile-updated')
            <div class="bg-status-active/10 border border-status-active/20 rounded-xl px-5 py-3 text-sm text-status-active flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
                Profile updated successfully.
            </div>
        @endif
        @if (session('status') === 'password-updated')
            <div class="bg-status-active/10 border border-status-active/20 rounded-xl px-5 py-3 text-sm text-status-active flex items-center gap-2">
                <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
                Password changed successfully.
            </div>
        @endif

        {{-- ── Profile form ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-card">
            <div class="px-6 py-5 border-b border-border-subtle flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">person</span>
                <div>
                    <h3 class="text-base font-semibold text-on-surface">Personal Information</h3>
                    <p class="text-xs text-outline mt-0.5">Visible to administrators. Your role cannot be changed here.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="px-6 py-6 space-y-5">
                @csrf
                @method('PUT')

                {{-- Row 1: First / Last name --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">First Name</label>
                        <input type="text" name="first_name"
                               value="{{ old('first_name', $profile->first_name) }}"
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('first_name') border-status-blocked @enderror">
                        @error('first_name') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Last Name</label>
                        <input type="text" name="last_name"
                               value="{{ old('last_name', $profile->last_name) }}"
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('last_name') border-status-blocked @enderror">
                        @error('last_name') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Display name + email --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Display Name</label>
                        <input type="text" name="name" required
                               value="{{ old('name', $user->name) }}"
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('name') border-status-blocked @enderror">
                        @error('name') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Email Address</label>
                        <input type="email" name="email" required
                               value="{{ old('email', $user->email) }}"
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('email') border-status-blocked @enderror">
                        @error('email') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Phone + Timezone --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Phone</label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $profile->phone) }}"
                               placeholder="+44 7700 000000"
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('phone') border-status-blocked @enderror">
                        @error('phone') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Timezone</label>
                        @php
                            $timezones = [
                                'Africa/Lagos'        => 'Africa/Lagos (WAT)',
                                'UTC'                 => 'UTC',
                                'Europe/London'       => 'Europe/London (GMT/BST)',
                                'Europe/Paris'        => 'Europe/Paris (CET/CEST)',
                                'Europe/Berlin'       => 'Europe/Berlin (CET/CEST)',
                                'America/New_York'    => 'America/New_York (EST/EDT)',
                                'America/Chicago'     => 'America/Chicago (CST/CDT)',
                                'America/Denver'      => 'America/Denver (MST/MDT)',
                                'America/Los_Angeles' => 'America/Los_Angeles (PST/PDT)',
                                'America/Toronto'     => 'America/Toronto (EST/EDT)',
                                'America/Vancouver'   => 'America/Vancouver (PST/PDT)',
                            ];
                        @endphp
                        <select name="timezone"
                                class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                       focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                       @error('timezone') border-status-blocked @enderror">
                            @foreach ($timezones as $tz => $label)
                                <option value="{{ $tz }}" @selected(old('timezone', $user->timezone) === $tz)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('timezone') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Country + City --}}
                @php $countries = \App\Support\CountryList::options(); @endphp
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Country</label>
                        <select name="country"
                                class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                       focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                       @error('country') border-status-blocked @enderror">
                            <option value="">— Select country —</option>
                            @foreach ($countries as $value => $label)
                                <option value="{{ $value }}" @selected(old('country', $profile->country) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('country') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">City</label>
                        <input type="text" name="city"
                               value="{{ old('city', $profile->city) }}"
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('city') border-status-blocked @enderror">
                        @error('city') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Short Description</label>
                    <textarea name="bio" rows="3" maxlength="500"
                              placeholder="A brief description about yourself…"
                              class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                     focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary resize-none
                                     @error('bio') border-status-blocked @enderror">{{ old('bio', $profile->bio) }}</textarea>
                    @error('bio') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-outline">Max 500 characters</p>
                </div>

                {{-- Read-only role display --}}
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Role</label>
                    <div class="w-full px-4 py-2.5 bg-surface-container-low border border-border-subtle rounded-lg text-sm text-on-surface-variant capitalize">
                        {{ str_replace('_', ' ', auth()->user()->getGvosRoleName()) }}
                    </div>
                    <p class="mt-1 text-xs text-outline">Contact an administrator to change your role.</p>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="bg-secondary hover:brightness-110 active:scale-[0.98] text-on-secondary text-sm font-semibold px-6 py-2.5 rounded-lg transition-all shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Password change form ──────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-card">
            <div class="px-6 py-5 border-b border-border-subtle flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">lock</span>
                <div>
                    <h3 class="text-base font-semibold text-on-surface">Change Password</h3>
                    <p class="text-xs text-outline mt-0.5">Choose a strong password of at least 8 characters.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="px-6 py-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Current Password</label>
                    <input type="password" name="current_password" required
                           class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                  focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                  @error('current_password') border-status-blocked @enderror">
                    @error('current_password') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">New Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary
                                      @error('password') border-status-blocked @enderror">
                        @error('password') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-surface-container-lowest
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="bg-on-surface hover:bg-on-background active:scale-[0.98] text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-all shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size: 16px;">lock_reset</span>
                        Change Password
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Back link ─────────────────────────────────────────────────── --}}
        <div class="text-center pb-4">
            <a href="{{ auth()->user()->getDashboardRoute() }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center justify-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Back to Dashboard
            </a>
        </div>

    </div>

</x-layouts.gvos>
