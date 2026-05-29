<x-layouts.gvos title="My Dashboard">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $talentProfile = $user->talentProfile;
    $myWorkspaces = \App\Models\Workspace::where(function ($q) use ($user) {
            $q->where('primary_talent_id', $user->id)
              ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('status', 'active'));
        })
        ->whereIn('status', ['pending', 'active'])
        ->count();
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">GVOS Talent Portal</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-1 rounded-full font-medium">
                Talent
            </span>
        </div>
    </div>

    {{-- ── Talent profile status card ───────────────────────────────────── --}}
    @if ($talentProfile)
    <div class="bg-white rounded-xl border border-slate-200 px-6 py-4 mb-6 flex items-center gap-4">
        <div class="flex-1">
            <p class="text-sm font-medium text-slate-700">Talent Profile</p>
            <p class="text-xs text-slate-400 mt-0.5">
                Training: <span class="font-medium text-slate-600">{{ ucwords(str_replace('_', ' ', $talentProfile->training_status)) }}</span>
                &nbsp;·&nbsp;
                Equipment: <span class="font-medium text-slate-600">{{ ucwords(str_replace('_', ' ', $talentProfile->equipment_status)) }}</span>
                @if ($talentProfile->talent_code)
                &nbsp;·&nbsp; Code: <span class="font-mono text-slate-600">{{ $talentProfile->talent_code }}</span>
                @endif
            </p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium
            @if($talentProfile->status === 'active') bg-emerald-50 text-emerald-700 border border-emerald-200
            @elseif($talentProfile->status === 'pending') bg-amber-50 text-amber-700 border border-amber-200
            @elseif($talentProfile->status === 'suspended') bg-red-50 text-red-700 border border-red-200
            @else bg-slate-100 text-slate-600 border border-slate-200
            @endif">
            {{ ucfirst($talentProfile->status) }}
        </span>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('profile.show') }}"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-sm font-semibold text-slate-800">My Profile</p>
            <p class="text-xs text-slate-400 mt-0.5">Update your details and password</p>
        </a>
        <a href="{{ route('workspace.index') }}"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-sm font-semibold text-slate-800">My Workspaces</p>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ $myWorkspaces > 0 ? $myWorkspaces . ' active workspace' . ($myWorkspaces !== 1 ? 's' : '') : 'No active workspaces yet' }}
            </p>
        </a>
        <div class="bg-white rounded-xl border border-dashed border-slate-200 px-5 py-4 opacity-50 cursor-not-allowed">
            <p class="text-sm font-semibold text-slate-500">Time Tracker</p>
            <p class="text-xs text-slate-400 mt-0.5">Coming in Phase 7</p>
        </div>
    </div>

    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-6 py-5">
        <p class="text-sm font-semibold text-emerald-800">Phase 4 — Workspace Engine</p>
        <p class="text-sm text-emerald-700 mt-0.5">
            Your workspaces are now visible. Task boards, time tracking and daily reports are coming in later phases.
        </p>
    </div>

</x-layouts.gvos>
