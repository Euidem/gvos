<x-layouts.public title="Request a Service">

@php
    $oldTz      = old('timezone', '');
    $isCustomTz = !empty($oldTz) && !array_key_exists($oldTz, $timezones);

    // Detect which step to restore to when Laravel returns validation errors
    $restoreStep = 1;
    if ($errors->has('first_name') || $errors->has('last_name') || $errors->has('email')) {
        $restoreStep = 1;
    } elseif ($errors->has('client_type')) {
        $restoreStep = 2;
    } elseif ($errors->has('estimated_hours_per_week') || $errors->has('preferred_start_date') || $errors->has('work_description')) {
        $restoreStep = 3;
    } elseif ($errors->any()) {
        $restoreStep = 4;
    }
@endphp

<div class="w-full max-w-6xl mx-auto">

    {{-- ── Top branding ──────────────────────────────────────────────────── --}}
    <div class="text-center mb-8">
        <span class="text-3xl font-bold text-white tracking-tight">GVOS</span>
        <p class="text-slate-400 text-sm mt-1">Operations Management Platform</p>
    </div>

    {{-- ── Two-column layout ─────────────────────────────────────────────── --}}
    <div class="flex flex-col-reverse lg:flex-row gap-8 lg:items-start">

        {{-- ══════════════════════════════════════════════════════════════════
             LEFT — Form card
        ══════════════════════════════════════════════════════════════════════ --}}
        <div class="flex-1 min-w-0">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

                {{-- Progress header --}}
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 sm:px-8 py-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h1 class="text-xl font-bold text-white leading-snug">Find the right remote support</h1>
                            <p class="text-indigo-200 text-sm mt-0.5">
                                Step <span id="step-current">1</span> of 4 —
                                <span id="step-label-inline">Your details</span>
                            </p>
                        </div>
                        <span class="text-xs text-indigo-200 border border-indigo-400 rounded-full px-3 py-1 mt-0.5 hidden sm:inline-flex whitespace-nowrap">
                            No payment required
                        </span>
                    </div>

                    {{-- Progress bar --}}
                    <div class="w-full bg-indigo-800/60 rounded-full h-1.5 mb-3">
                        <div id="progress-bar"
                             class="bg-white rounded-full h-1.5 transition-all duration-500 ease-out"
                             style="width: 25%">
                        </div>
                    </div>

                    {{-- Step labels (hidden on xs) --}}
                    <div class="hidden sm:flex justify-between text-xs">
                        <span class="step-nav-label" data-step="1">Your details</span>
                        <span class="step-nav-label" data-step="2">Support needed</span>
                        <span class="step-nav-label" data-step="3">Work details</span>
                        <span class="step-nav-label" data-step="4">Final details</span>
                    </div>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('lead.request-service.store') }}"
                      id="lead-form" novalidate class="px-6 sm:px-8 py-8">
                    @csrf

                    {{-- Server-side validation errors --}}
                    @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
                        <div class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-red-700 mb-1">Please correct the following:</p>
                                <ul class="text-sm text-red-600 list-disc list-inside space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ────────────────────────────────────────────────────
                         STEP 1 — Your Details
                    ──────────────────────────────────────────────────────── --}}
                    <div class="step-panel" id="step-1">
                        <div class="mb-7">
                            <h2 class="text-2xl font-bold text-slate-800">Tell us about yourself</h2>
                            <p class="text-sm text-slate-500 mt-1.5">We use this to set up your account and reach out with next steps.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                            <div>
                                <label for="first_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input id="first_name" type="text" name="first_name"
                                       value="{{ old('first_name') }}" maxlength="100" placeholder="Jane" autocomplete="given-name"
                                       class="w-full px-4 py-3 border rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors
                                              @error('first_name') border-red-400 bg-red-50 @else border-slate-300 @enderror">
                                @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Last Name <span class="text-red-500">*</span>
                                </label>
                                <input id="last_name" type="text" name="last_name"
                                       value="{{ old('last_name') }}" maxlength="100" placeholder="Smith" autocomplete="family-name"
                                       class="w-full px-4 py-3 border rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors
                                              @error('last_name') border-red-400 bg-red-50 @else border-slate-300 @enderror">
                                @error('last_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input id="email" type="email" name="email"
                                       value="{{ old('email') }}" maxlength="255" placeholder="jane@example.com" autocomplete="email"
                                       class="w-full px-4 py-3 border rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors
                                              @error('email') border-red-400 bg-red-50 @else border-slate-300 @enderror">
                                <p class="text-xs text-slate-400 mt-1">We'll send confirmation here. No spam, ever.</p>
                                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Phone Number <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <input id="phone" type="tel" name="phone"
                                       value="{{ old('phone') }}" maxlength="50" placeholder="+234 800 000 0000" autocomplete="tel"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            </div>

                            <div>
                                <label for="country" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Country <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <select id="country" name="country"
                                        class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors bg-white">
                                    <option value="">— Select country —</option>
                                    @foreach (\App\Support\CountryList::options() as $val => $lbl)
                                        <option value="{{ $val }}" @selected(old('country') === $val)>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="city" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    City <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <input id="city" type="text" name="city"
                                       value="{{ old('city') }}" maxlength="100" placeholder="e.g. Lagos, London" autocomplete="address-level2"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            </div>

                            <div>
                                <label for="timezone_select" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Your Timezone <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <select id="timezone_select" onchange="handleTimezoneChange(this)"
                                        class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors bg-white">
                                    <option value="">— Select timezone —</option>
                                    @foreach ($timezones as $tz => $label)
                                        <option value="{{ $tz }}" {{ $oldTz === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                    <option value="other" {{ $isCustomTz ? 'selected' : '' }}>Other (specify below)</option>
                                </select>
                                {{-- Hidden field carries the actual submitted value --}}
                                <input type="hidden" id="timezone_hidden" name="timezone" value="{{ $oldTz }}">
                            </div>

                            {{-- Custom timezone input (shown when "other" selected) --}}
                            <div id="custom-timezone-wrap" class="{{ $isCustomTz ? '' : 'hidden' }} sm:col-span-2">
                                <label for="timezone_custom" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Please specify your timezone
                                </label>
                                <input id="timezone_custom" type="text"
                                       value="{{ $isCustomTz ? $oldTz : '' }}"
                                       maxlength="100" placeholder="e.g. Asia/Kolkata, Pacific/Auckland, Africa/Nairobi"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                                <p class="text-xs text-slate-400 mt-1">Use a standard timezone identifier, e.g. Asia/Dubai</p>
                            </div>

                        </div>
                    </div>

                    {{-- ────────────────────────────────────────────────────
                         STEP 2 — Support Needed
                    ──────────────────────────────────────────────────────── --}}
                    <div class="step-panel hidden" id="step-2">
                        <div class="mb-7">
                            <h2 class="text-2xl font-bold text-slate-800">What kind of support do you need?</h2>
                            <p class="text-sm text-slate-500 mt-1.5">Choose your account type and describe the role you'd like filled.</p>
                        </div>

                        {{-- Client type --}}
                        <div class="mb-7">
                            <label class="block text-sm font-semibold text-slate-700 mb-3">
                                I am requesting support as… <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="client_type" value="individual"
                                           {{ old('client_type', 'individual') === 'individual' ? 'checked' : '' }}
                                           class="sr-only" onchange="handleClientType(this)">
                                    <div class="client-type-card border-2 rounded-xl p-5 transition-all
                                                {{ old('client_type', 'individual') === 'individual' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:border-slate-300' }}">
                                        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center mb-3">
                                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <p class="font-semibold text-slate-800 text-sm">Individual</p>
                                        <p class="text-xs text-slate-500 mt-0.5">I need support for myself</p>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="client_type" value="business"
                                           {{ old('client_type') === 'business' ? 'checked' : '' }}
                                           class="sr-only" onchange="handleClientType(this)">
                                    <div class="client-type-card border-2 rounded-xl p-5 transition-all
                                                {{ old('client_type') === 'business' ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:border-slate-300' }}">
                                        <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center mb-3">
                                            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                        <p class="font-semibold text-slate-800 text-sm">Business</p>
                                        <p class="text-xs text-slate-500 mt-0.5">I represent a company</p>
                                    </div>
                                </label>
                            </div>
                            @error('client_type')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        {{-- Business fields --}}
                        <div id="business-fields"
                             class="{{ old('client_type') === 'business' ? '' : 'hidden' }} grid grid-cols-1 sm:grid-cols-2 gap-5 mb-7 p-5 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide sm:col-span-2">Company Details</p>
                            <div>
                                <label for="company_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Company Name</label>
                                <input id="company_name" type="text" name="company_name"
                                       value="{{ old('company_name') }}" maxlength="255" placeholder="Acme Ltd"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-colors">
                            </div>
                            <div>
                                <label for="company_website" class="block text-sm font-semibold text-slate-700 mb-1.5">Company Website</label>
                                <input id="company_website" type="url" name="company_website"
                                       value="{{ old('company_website') }}" maxlength="255" placeholder="https://acme.com"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-colors">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="company_email_domain" class="block text-sm font-semibold text-slate-700 mb-1.5">Company Email Domain</label>
                                <input id="company_email_domain" type="text" name="company_email_domain"
                                       value="{{ old('company_email_domain') }}" maxlength="255" placeholder="e.g. acme.com"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white transition-colors">
                                <p class="text-xs text-slate-400 mt-1">Used to match staff email addresses during onboarding.</p>
                            </div>
                        </div>

                        {{-- Role cards --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">
                                What role do you need? <span class="text-xs font-normal text-slate-400">(optional)</span>
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                @php
                                    $roleData = [
                                        'virtual_assistant'    => ['d' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                            'bg' => 'bg-indigo-50',   'ic' => 'text-indigo-600',  'bsel' => 'border-indigo-500 bg-indigo-50'],
                                        'executive_assistant'  => ['d' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'bg' => 'bg-violet-50',   'ic' => 'text-violet-600',  'bsel' => 'border-violet-500 bg-violet-50'],
                                        'social_media_manager' => ['d' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z',                         'bg' => 'bg-pink-50',     'ic' => 'text-pink-600',    'bsel' => 'border-pink-500 bg-pink-50'],
                                        'video_editor'         => ['d' => 'M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'bg' => 'bg-red-50', 'ic' => 'text-red-500', 'bsel' => 'border-red-500 bg-red-50'],
                                        'developer'            => ['d' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',                                                                                 'bg' => 'bg-emerald-50',  'ic' => 'text-emerald-600', 'bsel' => 'border-emerald-500 bg-emerald-50'],
                                        'designer'             => ['d' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', 'bg' => 'bg-amber-50', 'ic' => 'text-amber-600', 'bsel' => 'border-amber-500 bg-amber-50'],
                                        'motion_graphics'      => ['d' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-orange-50', 'ic' => 'text-orange-600', 'bsel' => 'border-orange-500 bg-orange-50'],
                                        'other'                => ['d' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',                                                                                            'bg' => 'bg-slate-100',   'ic' => 'text-slate-500',   'bsel' => 'border-slate-500 bg-slate-50'],
                                    ];
                                @endphp
                                @foreach ($roles as $val => $lbl)
                                    @php $r = $roleData[$val] ?? ['d' => 'M12 6v6m0 0v6m0-6h6m-6 0H6','bg'=>'bg-slate-100','ic'=>'text-slate-500','bsel'=>'border-slate-500 bg-slate-50']; $sel = old('role_needed') === $val; @endphp
                                    <label class="cursor-pointer">
                                        <input type="radio" name="role_needed" value="{{ $val }}"
                                               {{ $sel ? 'checked' : '' }}
                                               class="sr-only" onchange="handleRoleChange(this)">
                                        <div class="role-card border-2 rounded-xl p-3 text-center transition-all hover:shadow-sm
                                                    {{ $sel ? $r['bsel'] : 'border-slate-200 hover:border-slate-300' }}"
                                             data-bsel="{{ $r['bsel'] }}">
                                            <div class="w-8 h-8 {{ $r['bg'] }} rounded-lg flex items-center justify-center mx-auto mb-2">
                                                <svg class="w-4 h-4 {{ $r['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $r['d'] }}"/>
                                                </svg>
                                            </div>
                                            <p class="text-xs font-medium text-slate-700 leading-tight">{{ $lbl }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            {{-- Other role text --}}
                            <div id="other-role-wrap" class="{{ old('role_needed') === 'other' ? '' : 'hidden' }} mt-4">
                                <label for="role_needed_other" class="block text-sm font-semibold text-slate-700 mb-1.5">Please describe the role</label>
                                <input id="role_needed_other" type="text" name="role_needed_other"
                                       value="{{ old('role_needed_other') }}" maxlength="255"
                                       placeholder="e.g. Research analyst, bookkeeper, data entry specialist"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            </div>
                        </div>
                    </div>

                    {{-- ────────────────────────────────────────────────────
                         STEP 3 — Work Details
                    ──────────────────────────────────────────────────────── --}}
                    <div class="step-panel hidden" id="step-3">
                        <div class="mb-7">
                            <h2 class="text-2xl font-bold text-slate-800">What will your assistant help with?</h2>
                            <p class="text-sm text-slate-500 mt-1.5">Help us understand your workload so we can match you well. All fields are optional.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="estimated_hours_per_week" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Estimated Hours / Week <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <input id="estimated_hours_per_week" type="number" name="estimated_hours_per_week"
                                       value="{{ old('estimated_hours_per_week') }}" min="1" max="168" placeholder="e.g. 20"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors
                                              @error('estimated_hours_per_week') border-red-400 bg-red-50 @enderror">
                                <p class="text-xs text-slate-400 mt-1">Typical engagements are 10–40 hours per week.</p>
                                @error('estimated_hours_per_week')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="preferred_start_date" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Preferred Start Date <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <input id="preferred_start_date" type="date" name="preferred_start_date"
                                       value="{{ old('preferred_start_date') }}"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors
                                              @error('preferred_start_date') border-red-400 bg-red-50 @enderror">
                                @error('preferred_start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="preferred_work_schedule" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Preferred Work Schedule <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <input id="preferred_work_schedule" type="text" name="preferred_work_schedule"
                                       value="{{ old('preferred_work_schedule') }}" maxlength="255"
                                       placeholder="e.g. Mon–Fri 9am–5pm WAT, Flexible hours, US business hours only"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="required_skills" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Required Skills <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <input id="required_skills" type="text" name="required_skills"
                                       value="{{ old('required_skills') }}" maxlength="1000"
                                       placeholder="e.g. Calendar management, Canva, email handling, Excel, Shopify"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                                <p class="text-xs text-slate-400 mt-1">Separate skills with commas. We use this to find the best match.</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label for="work_description" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Work Description <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <textarea id="work_description" name="work_description" rows="5"
                                          maxlength="5000"
                                          placeholder="Describe the day-to-day tasks and responsibilities you need support with. Be as specific as you like — the more detail, the better the match."
                                          class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-y transition-colors
                                                 @error('work_description') border-red-400 bg-red-50 @enderror">{{ old('work_description') }}</textarea>
                                <p class="text-xs text-slate-400 mt-1">Maximum 5,000 characters.</p>
                                @error('work_description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    {{-- ────────────────────────────────────────────────────
                         STEP 4 — Final Details
                    ──────────────────────────────────────────────────────── --}}
                    <div class="step-panel hidden" id="step-4">
                        <div class="mb-7">
                            <h2 class="text-2xl font-bold text-slate-800">Almost there — final details</h2>
                            <p class="text-sm text-slate-500 mt-1.5">No payment required at this stage. This helps us prepare the right estimate for you.</p>
                        </div>

                        <div class="space-y-6">

                            {{-- Budget range --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-3">
                                    What's your rough monthly budget? <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @php
                                        $budgetLabels = [
                                            'under_500' => ['label' => 'Under $500 / month',          'sub' => 'Ideal for part-time or focused tasks'],
                                            '500_1000'  => ['label' => '$500 – $1,000 / month',       'sub' => 'Part-time support, growing workload'],
                                            '1000_2000' => ['label' => '$1,000 – $2,000 / month',     'sub' => 'Committed part-time or light full-time'],
                                            '2000_3500' => ['label' => '$2,000 – $3,500 / month',     'sub' => 'Full-time or specialist support'],
                                            '3500_plus' => ['label' => '$3,500+ / month',             'sub' => 'High-demand or multiple roles'],
                                            'not_sure'  => ['label' => 'Not sure yet',                'sub' => 'We\'ll help you figure it out'],
                                        ];
                                    @endphp
                                    @foreach ($budgetRanges as $val => $originalLabel)
                                        @php $bd = $budgetLabels[$val] ?? ['label' => $originalLabel, 'sub' => '']; $bSel = old('budget_range') === $val; @endphp
                                        <label class="cursor-pointer">
                                            <input type="radio" name="budget_range" value="{{ $val }}"
                                                   {{ $bSel ? 'checked' : '' }}
                                                   class="sr-only budget-radio">
                                            <div class="budget-card border-2 rounded-xl px-4 py-3.5 transition-all hover:shadow-sm
                                                        {{ $bSel ? 'border-indigo-500 bg-indigo-50' : 'border-slate-200 hover:border-slate-300' }}">
                                                <p class="text-sm font-semibold text-slate-800">{{ $bd['label'] }}</p>
                                                @if($bd['sub'])<p class="text-xs text-slate-500 mt-0.5">{{ $bd['sub'] }}</p>@endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-slate-400 mt-2">This is a rough guide — your final estimate will be prepared by our team based on your exact requirements.</p>
                            </div>

                            {{-- Source --}}
                            <div>
                                <label for="source" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    How did you hear about GVOS? <span class="text-xs font-normal text-slate-400">(optional)</span>
                                </label>
                                <input id="source" type="text" name="source"
                                       value="{{ old('source') }}" maxlength="255"
                                       placeholder="e.g. Google search, LinkedIn, referred by a friend, Twitter"
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            </div>

                            {{-- Privacy note --}}
                            <div class="bg-slate-50 border border-slate-200 rounded-xl px-5 py-4">
                                <div class="flex items-start gap-3">
                                    <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <p class="text-xs text-slate-500 leading-relaxed">
                                        Your information is only used to review your support request and prepare the right next step.
                                        No payment is required at this stage.
                                    </p>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold text-sm py-4 rounded-xl transition-colors shadow-sm flex items-center justify-center gap-2">
                                <span>Submit My Request</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- ── Step navigation buttons ──────────────────────── --}}
                    <div id="step-nav" class="flex items-center justify-between mt-8 pt-6 border-t border-slate-100">
                        <button type="button" id="btn-back"
                                class="hidden items-center gap-1.5 px-5 py-2.5 text-sm font-medium text-slate-600 border border-slate-300 rounded-xl hover:bg-slate-50 active:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back
                        </button>
                        <span id="back-placeholder"></span>
                        <button type="button" id="btn-next"
                                class="flex items-center gap-1.5 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 rounded-xl transition-colors">
                            Next
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>

                </form>
            </div>

            <p class="text-center text-xs text-slate-500 mt-5">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 underline">Sign in here</a>
            </p>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             RIGHT — Side panel
        ══════════════════════════════════════════════════════════════════════ --}}
        <div class="w-full lg:w-80 xl:w-96 flex-shrink-0 space-y-5">

            {{-- Hero copy card --}}
            <div class="bg-white rounded-2xl shadow-sm p-7">
                <h2 class="text-lg font-bold text-slate-800 leading-snug mb-3">
                    Find the right remote support<br class="hidden sm:block"> without the guesswork.
                </h2>
                <p class="text-sm text-slate-500 leading-relaxed mb-6">
                    Tell us what you need, and the GVOS team will review your request, recommend the right support structure, and guide you through the next step.
                </p>
                <div class="space-y-3.5">
                    @foreach ([
                        'One-day trial before full commitment',
                        'Managed talents with supervision',
                        'Communication and work tracked inside GVOS',
                        'Built for individuals and growing teams',
                    ] as $bullet)
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-sm text-slate-600">{{ $bullet }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- What happens next --}}
            <div class="bg-white rounded-2xl shadow-sm p-7">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-5">What happens next</h3>
                <div class="space-y-5">
                    @foreach ([
                        ['step' => '1', 'title' => 'Submit your request',            'sub' => 'We receive your details instantly', 'active' => true],
                        ['step' => '2', 'title' => 'Our team reviews your needs',    'sub' => 'Typically within 1–2 business days', 'active' => false],
                        ['step' => '3', 'title' => 'We prepare the right estimate', 'sub' => 'Based on role, hours, and scope', 'active' => false],
                        ['step' => '4', 'title' => 'We approve a one-day trial',    'sub' => 'No commitment until you\'re satisfied', 'active' => false],
                    ] as $s)
                    <div class="flex items-start gap-4">
                        <div class="w-7 h-7 {{ $s['active'] ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500' }} text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0">
                            {{ $s['step'] }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $s['title'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $s['sub'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- CSS illustration panel --}}
            <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-950 rounded-2xl p-5 overflow-hidden relative select-none">
                {{-- Decorative blobs --}}
                <div class="absolute top-0 right-0 w-28 h-28 bg-indigo-500 opacity-10 rounded-full -translate-y-10 translate-x-10 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-20 h-20 bg-violet-500 opacity-10 rounded-full translate-y-8 -translate-x-6 pointer-events-none"></div>

                <p class="text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-4">Inside GVOS</p>

                {{-- Client card --}}
                <div class="bg-white/10 border border-white/15 rounded-xl p-3 mb-2.5 flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-400 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">J</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-white">Jane Smith</p>
                        <p class="text-xs text-indigo-200 truncate">Virtual Assistant · 20h/wk</p>
                    </div>
                    <span class="text-xs bg-amber-400 text-amber-900 font-semibold px-2 py-0.5 rounded-full flex-shrink-0">New</span>
                </div>

                {{-- Connector --}}
                <div class="flex justify-center my-1.5">
                    <div class="flex flex-col items-center gap-0.5">
                        <div class="w-px h-2 bg-indigo-400/40"></div>
                        <svg class="w-3.5 h-3.5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                {{-- Talent match --}}
                <div class="bg-white/10 border border-white/15 rounded-xl p-3 mb-2.5 flex items-center gap-3">
                    <div class="w-8 h-8 bg-emerald-400 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">T</div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-white">Talent Assigned</p>
                        <p class="text-xs text-indigo-200 truncate">Prequalified · Supervised</p>
                    </div>
                    <span class="text-xs bg-emerald-400 text-emerald-900 font-semibold px-2 py-0.5 rounded-full flex-shrink-0">Matched</span>
                </div>

                {{-- Trial tasks --}}
                <div class="bg-white/10 border border-white/15 rounded-xl p-3 mb-2.5">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-white">Trial In Progress</p>
                        <span class="text-xs bg-indigo-400 text-white font-semibold px-2 py-0.5 rounded-full">Trial</span>
                    </div>
                    <div class="space-y-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-3.5 h-3.5 border border-white/30 rounded flex-shrink-0"></div>
                            <p class="text-xs text-indigo-200">Email inbox management</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-3.5 h-3.5 bg-emerald-400 rounded flex-shrink-0 flex items-center justify-center">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                            <p class="text-xs text-indigo-300 line-through opacity-70">Calendar setup done</p>
                        </div>
                    </div>
                </div>

                {{-- Chat bubble --}}
                <div class="bg-white/10 border border-white/15 rounded-xl p-3">
                    <div class="flex items-start gap-2 mb-2">
                        <div class="w-6 h-6 bg-violet-400 rounded-full flex-shrink-0 flex items-center justify-center text-white text-xs font-bold">M</div>
                        <div class="bg-white/10 rounded-lg rounded-tl-none px-3 py-2 flex-1">
                            <p class="text-xs text-white">All tasks completed on time ✓</p>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <span class="text-xs bg-emerald-400 text-emerald-900 font-semibold px-2 py-0.5 rounded-full">Tracked ✓</span>
                    </div>
                </div>
            </div>

        </div>{{-- /side panel --}}
    </div>{{-- /two-column --}}
</div>{{-- /outer container --}}

<script>
(function() {
    var TOTAL = 4;
    var LABELS = ['Your details', 'Support needed', 'Work details', 'Final details'];
    var current = {{ $restoreStep }};

    // ── Show/hide steps ───────────────────────────────────────────────────

    function showStep(n) {
        document.querySelectorAll('.step-panel').forEach(function(p) {
            p.classList.add('hidden');
        });
        var panel = document.getElementById('step-' + n);
        if (panel) panel.classList.remove('hidden');

        // Progress bar
        document.getElementById('progress-bar').style.width = ((n / TOTAL) * 100) + '%';

        // Inline counter
        document.getElementById('step-current').textContent = n;
        document.getElementById('step-label-inline').textContent = LABELS[n - 1];

        // Header step nav labels
        document.querySelectorAll('.step-nav-label').forEach(function(el) {
            var s = parseInt(el.dataset.step);
            el.className = 'step-nav-label';
            if (s < n)       el.classList.add('text-indigo-300');
            else if (s === n) el.classList.add('font-semibold', 'text-white');
            else              el.classList.add('text-indigo-300', 'opacity-50');
        });

        // Back/next buttons
        var btnBack = document.getElementById('btn-back');
        var btnNext = document.getElementById('btn-next');
        var placeholder = document.getElementById('back-placeholder');

        if (n === 1) {
            btnBack.classList.remove('flex');
            btnBack.classList.add('hidden');
            placeholder.classList.remove('hidden');
        } else {
            btnBack.classList.add('flex');
            btnBack.classList.remove('hidden');
            placeholder.classList.add('hidden');
        }

        if (n === TOTAL) {
            btnNext.classList.add('hidden');
        } else {
            btnNext.classList.remove('hidden');
        }

        // Scroll form into view smoothly
        var form = document.getElementById('lead-form');
        if (form) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // ── Front-end validation per step ─────────────────────────────────────

    function validateStep(n) {
        var ok = true;

        if (n === 1) {
            var fn = document.getElementById('first_name');
            var ln = document.getElementById('last_name');
            var em = document.getElementById('email');

            [fn, ln].forEach(function(f) {
                if (!f.value.trim()) {
                    markError(f); ok = false;
                }
            });

            if (em) {
                if (!em.value.trim()) {
                    markError(em); ok = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em.value.trim())) {
                    markError(em); ok = false;
                }
            }
        }
        // Steps 2–4 have no required fields (client_type defaults to individual)
        return ok;
    }

    function markError(el) {
        el.classList.add('border-red-400', 'bg-red-50');
        el.focus();
    }

    // Clear error state on input
    document.addEventListener('input', function(e) {
        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT')) {
            e.target.classList.remove('border-red-400', 'bg-red-50');
        }
    });

    // ── Button event listeners ────────────────────────────────────────────

    document.getElementById('btn-next').addEventListener('click', function() {
        if (!validateStep(current)) return;
        applyTimezoneBeforeTransition();
        if (current < TOTAL) {
            current++;
            showStep(current);
        }
    });

    document.getElementById('btn-back').addEventListener('click', function() {
        if (current > 1) {
            current--;
            showStep(current);
        }
    });

    // ── Timezone: select vs custom ────────────────────────────────────────

    window.handleTimezoneChange = function(select) {
        var wrap = document.getElementById('custom-timezone-wrap');
        var hidden = document.getElementById('timezone_hidden');
        if (select.value === 'other') {
            wrap.classList.remove('hidden');
            hidden.value = '';
        } else {
            wrap.classList.add('hidden');
            document.getElementById('timezone_custom').value = '';
            hidden.value = select.value;
        }
    };

    function applyTimezoneBeforeTransition() {
        var sel = document.getElementById('timezone_select');
        var hidden = document.getElementById('timezone_hidden');
        if (sel.value === 'other') {
            var custom = document.getElementById('timezone_custom').value.trim();
            hidden.value = custom;
        } else {
            hidden.value = sel.value;
        }
    }

    // Sync on submit too
    document.getElementById('lead-form').addEventListener('submit', function() {
        applyTimezoneBeforeTransition();
    });

    // ── Client type cards ─────────────────────────────────────────────────

    window.handleClientType = function(radio) {
        document.querySelectorAll('.client-type-card').forEach(function(c) {
            c.classList.remove('border-indigo-500', 'bg-indigo-50');
            c.classList.add('border-slate-200');
        });
        var card = radio.closest('label').querySelector('.client-type-card');
        if (card) {
            card.classList.remove('border-slate-200');
            card.classList.add('border-indigo-500', 'bg-indigo-50');
        }
        var bf = document.getElementById('business-fields');
        if (bf) bf.classList.toggle('hidden', radio.value !== 'business');
    };

    // ── Role cards ────────────────────────────────────────────────────────

    window.handleRoleChange = function(radio) {
        document.querySelectorAll('.role-card').forEach(function(c) {
            var bsel = c.dataset.bsel || 'border-indigo-500 bg-indigo-50';
            bsel.split(' ').forEach(function(cls) { c.classList.remove(cls); });
            c.classList.add('border-slate-200');
        });
        var card = radio.closest('label').querySelector('.role-card');
        if (card) {
            card.classList.remove('border-slate-200');
            var bsel = card.dataset.bsel || 'border-indigo-500 bg-indigo-50';
            bsel.split(' ').forEach(function(cls) { card.classList.add(cls); });
        }
        var wrap = document.getElementById('other-role-wrap');
        if (wrap) wrap.classList.toggle('hidden', radio.value !== 'other');
    };

    // ── Budget cards ──────────────────────────────────────────────────────

    document.querySelectorAll('.budget-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.budget-card').forEach(function(c) {
                c.classList.remove('border-indigo-500', 'bg-indigo-50');
                c.classList.add('border-slate-200');
            });
            var card = radio.closest('label').querySelector('.budget-card');
            if (card) {
                card.classList.remove('border-slate-200');
                card.classList.add('border-indigo-500', 'bg-indigo-50');
            }
        });
    });

    // ── Initialise on DOM ready ───────────────────────────────────────────

    document.addEventListener('DOMContentLoaded', function() {
        showStep(current);

        // Re-apply client type on restore
        var ct = document.querySelector('input[name="client_type"]:checked');
        if (ct) handleClientType(ct);

        // Re-apply role selection on restore
        var rr = document.querySelector('input[name="role_needed"]:checked');
        if (rr) handleRoleChange(rr);

        // Re-apply budget selection on restore
        document.querySelectorAll('.budget-radio:checked').forEach(function(r) {
            var card = r.closest('label').querySelector('.budget-card');
            if (card) {
                card.classList.remove('border-slate-200');
                card.classList.add('border-indigo-500', 'bg-indigo-50');
            }
        });

        // Timezone: if old value was custom, hidden field is already pre-filled by Blade
        var tzSel = document.getElementById('timezone_select');
        var tzHidden = document.getElementById('timezone_hidden');
        if (tzSel && tzSel.value !== 'other' && tzSel.value !== '') {
            tzHidden.value = tzSel.value;
        }
    });

}());
</script>
</x-layouts.public>
