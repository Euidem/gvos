<x-layouts.gvos title="My Dashboard">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">GVOS Client Portal</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-violet-50 text-violet-700 border border-violet-200 px-3 py-1 rounded-full font-medium">
                Client
            </span>
        </div>
    </div>

    {{-- ── Client profile status card ────────────────────────────────────── --}}
    @if ($clientProfile)
    <div class="bg-white rounded-xl border border-slate-200 px-6 py-4 mb-6 flex items-center gap-4">
        <div class="flex-1">
            <p class="text-sm font-medium text-slate-700">Client Profile</p>
            <p class="text-xs text-slate-400 mt-0.5">
                @if ($clientProfile->service_interest)
                    Service interest: <span class="font-medium text-slate-600">{{ $clientProfile->service_interest }}</span>
                @else
                    Complete your profile to speed up onboarding.
                @endif
            </p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium
            @if($clientProfile->status === 'active') bg-emerald-50 text-emerald-700 border border-emerald-200
            @elseif($clientProfile->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200
            @elseif($clientProfile->status === 'suspended') bg-red-50 text-red-700 border border-red-200
            @else bg-slate-100 text-slate-600 border border-slate-200
            @endif">
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
            <p class="text-sm font-semibold text-slate-500">My Workspace</p>
            <p class="text-xs text-slate-400 mt-0.5">Coming in Phase 4</p>
        </div>
        <div class="bg-white rounded-xl border border-dashed border-slate-200 px-5 py-4 opacity-50 cursor-not-allowed">
            <p class="text-sm font-semibold text-slate-500">Billing</p>
            <p class="text-xs text-slate-400 mt-0.5">Coming in Phase 8</p>
        </div>
    </div>

    <div class="bg-violet-50 border border-violet-200 rounded-xl px-6 py-5">
        <p class="text-sm font-semibold text-violet-800">Phase 2 — People and Organization Foundation</p>
        <p class="text-sm text-violet-700 mt-0.5">
            Your client profile has been set up. Workspace, task board, files and billing features are coming in later phases.
        </p>
    </div>

</x-layouts.gvos>
