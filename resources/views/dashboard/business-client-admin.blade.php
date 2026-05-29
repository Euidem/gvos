<x-layouts.gvos title="Business Account">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;
    $company = $clientProfile?->company;
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">GVOS Client Portal — Business Account</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-violet-50 text-violet-700 border border-violet-200 px-3 py-1 rounded-full font-medium">
                Business Admin
            </span>
        </div>
    </div>

    {{-- ── Company / client profile status card ─────────────────────────── --}}
    @if ($company)
    <div class="bg-white rounded-xl border border-slate-200 px-6 py-4 mb-6 flex items-center gap-4">
        <div class="flex-1">
            <p class="text-sm font-medium text-slate-700">{{ $company->name }}</p>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $company->industry ?? 'Business account' }}
                @if ($company->country) &nbsp;·&nbsp; {{ $company->country }} @endif
            </p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium
            @if($company->status === 'active') bg-emerald-50 text-emerald-700 border border-emerald-200
            @elseif($company->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200
            @elseif($company->status === 'suspended') bg-red-50 text-red-700 border border-red-200
            @else bg-slate-100 text-slate-600 border border-slate-200
            @endif">
            {{ ucfirst($company->status) }}
        </span>
    </div>
    @elseif ($clientProfile)
    <div class="bg-white rounded-xl border border-slate-200 px-6 py-4 mb-6 flex items-center gap-4">
        <div class="flex-1">
            <p class="text-sm font-medium text-slate-700">Client Profile</p>
            <p class="text-xs text-slate-400 mt-0.5">Your account is being set up by our team.</p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-amber-50 text-amber-700 border border-amber-200">
            {{ ucfirst($clientProfile->status) }}
        </span>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('profile.show') }}"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-sm font-semibold text-slate-800">My Profile</p>
            <p class="text-xs text-slate-400 mt-0.5">Update your details and password</p>
        </a>
        <div class="bg-white rounded-xl border border-dashed border-slate-200 px-5 py-4 opacity-50 cursor-not-allowed">
            <p class="text-sm font-semibold text-slate-500">Company &amp; Staff</p>
            <p class="text-xs text-slate-400 mt-0.5">Coming in Phase 3</p>
        </div>
        <div class="bg-white rounded-xl border border-dashed border-slate-200 px-5 py-4 opacity-50 cursor-not-allowed">
            <p class="text-sm font-semibold text-slate-500">Workspace</p>
            <p class="text-xs text-slate-400 mt-0.5">Coming in Phase 4</p>
        </div>
    </div>

    <div class="bg-violet-50 border border-violet-200 rounded-xl px-6 py-5">
        <p class="text-sm font-semibold text-violet-800">Phase 2 — People and Organization Foundation</p>
        <p class="text-sm text-violet-700 mt-0.5">
            Your business account is active. Company management, staff invitations and workspace features are coming in later phases.
        </p>
    </div>

</x-layouts.gvos>
