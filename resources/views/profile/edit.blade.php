<x-layouts.gvos title="My Profile">

    <div class="max-w-2xl mx-auto space-y-8">

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <div>
            <h2 class="text-2xl font-bold text-slate-800">My Profile</h2>
            <p class="text-sm text-slate-500 mt-1">Update your personal information and account settings.</p>
        </div>

        {{-- ── Success banners ──────────────────────────────────────────── --}}
        @if (session('status') === 'profile-updated')
            <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-700">
                ✓ Profile updated successfully.
            </div>
        @endif
        @if (session('status') === 'password-updated')
            <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 text-sm text-green-700">
                ✓ Password changed successfully.
            </div>
        @endif

        {{-- ── Profile form ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-800">Personal Information</h3>
                <p class="text-xs text-slate-400 mt-0.5">Visible to administrators. Your role cannot be changed here.</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="px-6 py-6 space-y-5">
                @csrf
                @method('PUT')

                {{-- Row 1: First / Last name --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">First Name</label>
                        <input type="text" name="first_name"
                               value="{{ old('first_name', $profile->first_name) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('first_name') border-red-400 @enderror">
                        @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Last Name</label>
                        <input type="text" name="last_name"
                               value="{{ old('last_name', $profile->last_name) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('last_name') border-red-400 @enderror">
                        @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Display name + email --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Display Name</label>
                        <input type="text" name="name" required
                               value="{{ old('name', $user->name) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-400 @enderror">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" required
                               value="{{ old('email', $user->email) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Phone + Timezone --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone</label>
                        <input type="text" name="phone"
                               value="{{ old('phone', $profile->phone) }}"
                               placeholder="+44 7700 000000"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('phone') border-red-400 @enderror">
                        @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Timezone</label>
                        <select name="timezone"
                                class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('timezone') border-red-400 @enderror">
                            @foreach (timezone_identifiers_list() as $tz)
                                <option value="{{ $tz }}" @selected(old('timezone', $user->timezone) === $tz)>{{ $tz }}</option>
                            @endforeach
                        </select>
                        @error('timezone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Country + City --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Country</label>
                        <input type="text" name="country"
                               value="{{ old('country', $profile->country) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('country') border-red-400 @enderror">
                        @error('country') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">City</label>
                        <input type="text" name="city"
                               value="{{ old('city', $profile->city) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('city') border-red-400 @enderror">
                        @error('city') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Bio --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Short Description</label>
                    <textarea name="bio" rows="3" maxlength="500"
                              placeholder="A brief description about yourself…"
                              class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none @error('bio') border-red-400 @enderror">{{ old('bio', $profile->bio) }}</textarea>
                    @error('bio') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-400">Max 500 characters</p>
                </div>

                {{-- Read-only role display --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                    <div class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 capitalize">
                        {{ str_replace('_', ' ', auth()->user()->getGvosRoleName()) }}
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Contact an administrator to change your role.</p>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Password change form ──────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-base font-semibold text-slate-800">Change Password</h3>
                <p class="text-xs text-slate-400 mt-0.5">Choose a strong password of at least 8 characters.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}" class="px-6 py-6 space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
                    <input type="password" name="current_password" required
                           class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('current_password') border-red-400 @enderror">
                    @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold px-6 py-2.5 rounded-lg transition-colors">
                        Change Password
                    </button>
                </div>
            </form>
        </div>

        {{-- ── Back link ─────────────────────────────────────────────────── --}}
        <div class="text-center pb-4">
            <a href="{{ auth()->user()->getDashboardRoute() }}"
               class="text-sm text-indigo-600 hover:text-indigo-500">
                ← Back to Dashboard
            </a>
        </div>

    </div>

</x-layouts.gvos>
