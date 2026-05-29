<x-layouts.gvos title="My Dashboard">
@php $user = auth()->user(); $profile = $user->profile; @endphp

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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('profile.show') }}"
           class="bg-white rounded-xl border border-slate-200 px-5 py-4 hover:border-indigo-300 hover:shadow-sm transition-all">
            <p class="text-sm font-semibold text-slate-800">My Profile</p>
            <p class="text-xs text-slate-400 mt-0.5">Update your details and password</p>
        </a>
        <div class="bg-white rounded-xl border border-dashed border-slate-200 px-5 py-4 opacity-50 cursor-not-allowed">
            <p class="text-sm font-semibold text-slate-500">My Tasks</p>
            <p class="text-xs text-slate-400 mt-0.5">Coming in Phase 5</p>
        </div>
        <div class="bg-white rounded-xl border border-dashed border-slate-200 px-5 py-4 opacity-50 cursor-not-allowed">
            <p class="text-sm font-semibold text-slate-500">Time Tracker</p>
            <p class="text-xs text-slate-400 mt-0.5">Coming in Phase 7</p>
        </div>
    </div>

    <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-6 py-5">
        <p class="text-sm font-semibold text-emerald-800">Phase 1 — Identity and Access Foundation</p>
        <p class="text-sm text-emerald-700 mt-0.5">
            Your account is active. Task boards, time tracking and daily reports are coming in later phases.
        </p>
    </div>

</x-layouts.gvos>
