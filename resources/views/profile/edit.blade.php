<x-layouts.gvos title="My Profile">

    {{-- ── Page header ────────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <h1 class="font-headline-lg text-headline-lg text-primary">My Profile</h1>
        <p class="text-[12px] text-outline mt-1">Update your personal information and account settings.</p>
    </div>

    {{-- ── Success banners ──────────────────────────────────────────────────── --}}
    @if (session('status') === 'profile-updated')
        <x-portal.alert type="success" class="mb-5">Profile updated successfully.</x-portal.alert>
    @endif
    @if (session('status') === 'password-updated')
        <x-portal.alert type="success" class="mb-5">Password changed successfully.</x-portal.alert>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-5xl">

        {{-- ── Sidebar — user card ──────────────────────────────────────────── --}}
        <div class="lg:col-span-1 order-first lg:order-last">
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-6 lg:sticky lg:top-6">
                {{-- Avatar --}}
                <div class="flex flex-col items-center text-center mb-5">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold mb-3"
                         style="background:linear-gradient(135deg,#0058be,#2170e4);">
                        {{ strtoupper(substr(auth()->user()->name ?? '?', 0, 1)) }}
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface">{{ auth()->user()->name }}</h3>
                    <p class="text-xs text-outline mt-0.5">{{ auth()->user()->email }}</p>
                    <div class="mt-2">
                        <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold"
                              style="background:rgba(0,88,190,0.08);color:#0058be;">
                            {{ ucwords(str_replace('_', ' ', auth()->user()->getGvosRoleName())) }}
                        </span>
                    </div>
                </div>

                <div class="space-y-2 text-sm border-t border-border-subtle pt-4">
                    @if ($profile->phone)
                        <div class="flex items-center gap-2 text-on-surface-variant">
                            <span class="material-symbols-outlined text-outline" style="font-size:15px;">phone</span>
                            <span class="text-xs">{{ $profile->phone }}</span>
                        </div>
                    @endif
                    @if ($profile->city || $profile->country)
                        <div class="flex items-center gap-2 text-on-surface-variant">
                            <span class="material-symbols-outlined text-outline" style="font-size:15px;">location_on</span>
                            <span class="text-xs">{{ implode(', ', array_filter([$profile->city, $profile->country])) }}</span>
                        </div>
                    @endif
                    @if ($user->timezone)
                        <div class="flex items-center gap-2 text-on-surface-variant">
                            <span class="material-symbols-outlined text-outline" style="font-size:15px;">schedule</span>
                            <span class="text-xs">{{ $user->timezone }}</span>
                        </div>
                    @endif
                </div>

                @if ($profile->bio)
                    <div class="mt-4 pt-4 border-t border-border-subtle">
                        <p class="text-xs text-on-surface-variant leading-relaxed">{{ $profile->bio }}</p>
                    </div>
                @endif

                <div class="mt-5 pt-4 border-t border-border-subtle space-y-2">
                    <a href="{{ route('notifications.index') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-secondary transition-all border border-border-subtle">
                        <span class="material-symbols-outlined" style="font-size:15px;">notifications</span>
                        Notifications
                    </a>
                    <a href="{{ route('settings.notifications') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-on-surface-variant hover:bg-surface-container-low hover:text-secondary transition-all border border-border-subtle">
                        <span class="material-symbols-outlined" style="font-size:15px;">tune</span>
                        Notification Preferences
                    </a>
                    <a href="{{ auth()->user()->getDashboardRoute() }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-secondary hover:brightness-110 transition-all border border-secondary/20"
                       style="background:rgba(0,88,190,0.04);">
                        <span class="material-symbols-outlined" style="font-size:15px;">dashboard</span>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        {{-- ── Main forms ────────────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Personal Information form --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-border-subtle flex items-center gap-3"
                     style="background:rgba(247,249,251,1);">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">person</span>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface">Personal Information</h3>
                        <p class="text-xs text-outline mt-0.5">Visible to administrators. Your role cannot be changed here.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="px-6 py-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">First Name</label>
                            <input type="text" name="first_name"
                                   value="{{ old('first_name', $profile->first_name) }}"
                                   class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
                                          @error('first_name') border-status-blocked @enderror">
                            @error('first_name') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Last Name</label>
                            <input type="text" name="last_name"
                                   value="{{ old('last_name', $profile->last_name) }}"
                                   class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
                                          @error('last_name') border-status-blocked @enderror">
                            @error('last_name') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Display Name</label>
                            <input type="text" name="name" required
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
                                          @error('name') border-status-blocked @enderror">
                            @error('name') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Email Address</label>
                            <input type="email" name="email" required
                                   value="{{ old('email', $user->email) }}"
                                   class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
                                          @error('email') border-status-blocked @enderror">
                            @error('email') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Phone</label>
                            <input type="text" name="phone"
                                   value="{{ old('phone', $profile->phone) }}"
                                   placeholder="+44 7700 000000"
                                   class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
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
                                    class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                           focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
                                           @error('timezone') border-status-blocked @enderror">
                                @foreach ($timezones as $tz => $label)
                                    <option value="{{ $tz }}" @selected(old('timezone', $user->timezone) === $tz)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('timezone') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    @php $countries = \App\Support\CountryList::options(); @endphp
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Country</label>
                            <select name="country"
                                    class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                           focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
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
                                   class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
                                          @error('city') border-status-blocked @enderror">
                            @error('city') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Short Description</label>
                        <textarea name="bio" rows="3" maxlength="500"
                                  placeholder="A brief description about yourself…"
                                  class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                         focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors resize-none
                                         @error('bio') border-status-blocked @enderror">{{ old('bio', $profile->bio) }}</textarea>
                        @error('bio') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-outline">Max 500 characters</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Role</label>
                        <div class="w-full px-4 py-2.5 bg-surface-container-low border border-border-subtle rounded-lg text-sm text-on-surface-variant capitalize">
                            {{ str_replace('_', ' ', auth()->user()->getGvosRoleName()) }}
                        </div>
                        <p class="mt-1 text-xs text-outline">Contact an administrator to change your role.</p>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold text-white hover:brightness-110 active:scale-[0.98] transition-all shadow-sm"
                                style="background:#0058be;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            {{-- Password change form --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-border-subtle flex items-center gap-3"
                     style="background:rgba(247,249,251,1);">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">lock</span>
                    <div>
                        <h3 class="text-sm font-bold text-on-surface">Change Password</h3>
                        <p class="text-xs text-outline mt-0.5">Choose a strong password of at least 8 characters.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('password.update') }}" class="px-6 py-6 space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                      focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
                                      @error('current_password') border-status-blocked @enderror">
                        @error('current_password') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">New Password</label>
                            <input type="password" name="password" required
                                   class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors
                                          @error('password') border-status-blocked @enderror">
                            @error('password') <p class="mt-1 text-xs text-status-blocked">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1.5">Confirm New Password</label>
                            <input type="password" name="password_confirmation" required
                                   class="w-full px-4 py-2.5 border border-border-subtle rounded-lg text-on-surface text-sm bg-white
                                          focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary transition-colors">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold text-white hover:brightness-110 active:scale-[0.98] transition-all shadow-sm"
                                style="background:#191c1e;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">lock_reset</span>
                            Change Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</x-layouts.gvos>
