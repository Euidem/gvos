<x-layouts.gvos title="Business Account">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;
    $company = $clientProfile?->company;
    $myWorkspaces = \App\Models\Workspace::whereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('status', 'active'))
        ->whereIn('status', ['pending', 'active'])
        ->count();
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-on-surface">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-on-surface-variant mt-1">GVOS Client Portal — Business Account</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-status-active/10 text-status-active border border-status-active/20 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-secondary/5 text-secondary border border-secondary/20 px-3 py-1 rounded-full font-medium">
                Business Admin
            </span>
        </div>
    </div>

    {{-- ── Company / client profile status card ─────────────────────────── --}}
    @if ($company)
    <div class="bg-white rounded-xl border border-border-subtle px-6 py-4 mb-6 flex items-center gap-4 shadow-card">
        <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">business</span>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-on-surface">{{ $company->name }}</p>
            <p class="text-xs text-outline mt-0.5">
                {{ $company->industry ?? 'Business account' }}
                @if ($company->country) &nbsp;·&nbsp; {{ $company->country }} @endif
            </p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium
            @if($company->status === 'active') bg-status-active/10 text-status-active border border-status-active/20
            @elseif($company->status === 'pending') bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20
            @elseif($company->status === 'suspended') bg-status-blocked/10 text-status-blocked border border-status-blocked/20
            @else bg-surface-container-low text-on-surface-variant border border-border-subtle
            @endif">
            {{ ucfirst($company->status) }}
        </span>
    </div>
    @elseif ($clientProfile)
    <div class="bg-white rounded-xl border border-border-subtle px-6 py-4 mb-6 flex items-center gap-4 shadow-card">
        <div class="flex-1">
            <p class="text-sm font-medium text-on-surface">Client Profile</p>
            <p class="text-xs text-outline mt-0.5">Your account is being set up by our team.</p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20">
            {{ ucfirst($clientProfile->status) }}
        </span>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('profile.show') }}"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">person</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface">My Profile</p>
                    <p class="text-xs text-outline mt-0.5">Update your details and password</p>
                </div>
            </div>
        </a>
        <div class="bg-white rounded-xl border border-dashed border-border-subtle px-5 py-4 opacity-50 cursor-not-allowed shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-surface-container-low rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-outline" style="font-size: 18px;">group</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface-variant">Company &amp; Staff</p>
                    <p class="text-xs text-outline mt-0.5">Coming in Phase 5</p>
                </div>
            </div>
        </div>
        <a href="{{ route('workspace.index') }}"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">workspaces</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface">My Workspace</p>
                    <p class="text-xs text-outline mt-0.5">
                        {{ $myWorkspaces > 0 ? $myWorkspaces . ' active workspace' . ($myWorkspaces !== 1 ? 's' : '') : 'No active workspaces yet' }}
                    </p>
                </div>
            </div>
        </a>
    </div>

    <div class="bg-secondary/5 border border-secondary/20 rounded-xl px-6 py-5 flex items-start gap-3">
        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size: 18px;">info</span>
        <div>
            <p class="text-sm font-semibold text-secondary">Phase 4 — Workspace Engine</p>
            <p class="text-sm text-on-surface-variant mt-0.5">
                Your workspace is now accessible. Staff invitations, task board and billing are coming in later phases.
            </p>
        </div>
    </div>

</x-layouts.gvos>
