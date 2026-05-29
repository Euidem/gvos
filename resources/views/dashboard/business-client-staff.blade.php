<x-layouts.gvos title="My Dashboard">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;
    $myWorkspaces = \App\Models\Workspace::whereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('status', 'active'))
        ->whereIn('status', ['pending', 'active'])
        ->count();
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">GVOS Client Portal — Business Staff</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-slate-100 text-slate-600 border border-slate-200 px-3 py-1 rounded-full font-medium">
                Business Staff
            </span>
        </div>
    </div>

    {{-- ── Client profile status card ────────────────────────────────────── --}}
    @if ($clientProfile)
    <div class="bg-white rounded-xl border border-slate-200 px-6 py-4 mb-6 flex items-center gap-4">
        <div class="flex-1">
            <p class="text-sm font-medium text-slate-700">
                {{ $clientProfile->company?->name ?? 'Staff Account' }}
            </p>
            <p class="text-xs text-slate-400 mt-0.5">
                @if ($clientProfile->job_title) {{ $clientProfile->job_title }} @else Your staff account is being set up. @endif
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

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        <a href="{{ route('profile.show') }}"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-sm font-semibold text-slate-800">My Profile</p>
            <p class="text-xs text-slate-400 mt-0.5">Update your details and password</p>
        </a>
        <a href="{{ route('workspace.index') }}"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-slate-300 hover:shadow-sm transition-all">
            <p class="text-sm font-semibold text-slate-800">Workspace Access</p>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $myWorkspaces > 0 ? $myWorkspaces . ' active workspace' . ($myWorkspaces !== 1 ? 's' : '') : 'No active workspaces yet' }}
            </p>
        </a>
    </div>

    <div class="bg-slate-50 border border-slate-200 rounded-xl px-6 py-5">
        <p class="text-sm font-semibold text-slate-700">Phase 4 — Workspace Engine</p>
        <p class="text-sm text-slate-500 mt-0.5">
            Your workspace access is now available. Task board and collaboration features are coming in later phases.
        </p>
    </div>

</x-layouts.gvos>
