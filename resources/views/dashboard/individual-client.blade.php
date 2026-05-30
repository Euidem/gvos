<x-layouts.gvos title="My Dashboard">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;
    $myWorkspaces = \App\Models\Workspace::whereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('status', 'active'))
        ->whereIn('status', ['pending', 'active'])
        ->count();

    // Task counts (Phase 5)
    $clientWorkspaceIds  = \App\Models\WorkspaceMember::where('user_id', $user->id)->where('status', 'active')->pluck('workspace_id');
    $clientOpenTasks     = \App\Models\WorkspaceTask::whereIn('workspace_id', $clientWorkspaceIds)->whereIn('status', ['pending', 'in_progress', 'blocked'])->count();
    $clientSubmittedTasks = \App\Models\WorkspaceTask::whereIn('workspace_id', $clientWorkspaceIds)->where('status', 'submitted')->count();
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-on-surface">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-on-surface-variant mt-1">GVOS Client Portal</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-status-active/10 text-status-active border border-status-active/20 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-secondary/5 text-secondary border border-secondary/20 px-3 py-1 rounded-full font-medium">
                Client
            </span>
        </div>
    </div>

    {{-- ── Client profile status card ────────────────────────────────────── --}}
    @if ($clientProfile)
    <div class="bg-white rounded-xl border border-border-subtle px-6 py-4 mb-6 flex items-center gap-4 shadow-card">
        <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">person</span>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-on-surface">Client Profile</p>
            <p class="text-xs text-outline mt-0.5">
                @if ($clientProfile->service_interest)
                    Service interest: <span class="font-medium text-on-surface-variant">{{ $clientProfile->service_interest }}</span>
                @else
                    Complete your profile to speed up onboarding.
                @endif
            </p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium
            @if($clientProfile->status === 'active') bg-status-active/10 text-status-active border border-status-active/20
            @elseif($clientProfile->status === 'pending') bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20
            @elseif($clientProfile->status === 'suspended') bg-status-blocked/10 text-status-blocked border border-status-blocked/20
            @else bg-surface-container-low text-on-surface-variant border border-border-subtle
            @endif">
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
        <div class="bg-white rounded-xl border border-dashed border-border-subtle px-5 py-4 opacity-50 cursor-not-allowed shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-surface-container-low rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-outline" style="font-size: 18px;">receipt_long</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface-variant">Billing</p>
                    <p class="text-xs text-outline mt-0.5">Coming in Phase 8</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Task counts (Phase 5) ──────────────────────────────────────── --}}
    @if ($clientOpenTasks > 0 || $clientSubmittedTasks > 0)
    <div class="mb-8">
        <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">Workspace Tasks</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('workspace.index') }}"
               class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-secondary">{{ $clientOpenTasks }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Open Tasks</p>
            </a>
            <a href="{{ route('workspace.index') }}"
               class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-status-trial/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-status-trial">{{ $clientSubmittedTasks }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Submitted — Awaiting Approval</p>
            </a>
        </div>
    </div>
    @endif

    <div class="bg-secondary/5 border border-secondary/20 rounded-xl px-6 py-5 flex items-start gap-3">
        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size: 18px;">info</span>
        <div>
            <p class="text-sm font-semibold text-secondary">Phase 5 — Task Board</p>
            <p class="text-sm text-on-surface-variant mt-0.5">
                Task boards are now live in your workspace. Create tasks, review submitted work and approve deliverables.
            </p>
        </div>
    </div>

</x-layouts.gvos>
