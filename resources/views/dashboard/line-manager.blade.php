<x-layouts.gvos title="Manager Dashboard">
@php
    $user = auth()->user();
    $profile = $user->profile;
    $managerProfile = $user->managerProfile;
    $myWorkspaces = \App\Models\Workspace::where(function ($q) use ($user) {
            $q->where('primary_manager_id', $user->id)
              ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('status', 'active'));
        })
        ->whereIn('status', ['pending', 'active'])
        ->count();

    // Task counts for managed workspaces (Phase 5)
    $managedWorkspaceIds = \App\Models\Workspace::where(function ($q) use ($user) {
            $q->where('primary_manager_id', $user->id)
              ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('role', 'manager')->where('status', 'active'));
        })->pluck('id');
    $managerTasksOpen      = \App\Models\WorkspaceTask::whereIn('workspace_id', $managedWorkspaceIds)
        ->whereIn('status', ['pending', 'in_progress', 'blocked', 'revision_requested'])->count();
    $managerTasksSubmitted = \App\Models\WorkspaceTask::whereIn('workspace_id', $managedWorkspaceIds)
        ->where('status', 'submitted')->count();
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-on-surface">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-on-surface-variant mt-1">GVOS Manager Console</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-status-active/10 text-status-active border border-status-active/20 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-secondary/5 text-secondary border border-secondary/20 px-3 py-1 rounded-full font-medium">
                Line Manager
            </span>
        </div>
    </div>

    {{-- ── Manager profile status card ──────────────────────────────────── --}}
    @if ($managerProfile)
    <div class="bg-white rounded-xl border border-border-subtle px-6 py-4 mb-6 flex items-center gap-4 shadow-card">
        <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">manage_accounts</span>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-on-surface">Manager Profile</p>
            <p class="text-xs text-outline mt-0.5">
                Capacity: <span class="font-medium text-on-surface-variant">{{ $managerProfile->current_load }} / {{ $managerProfile->capacity_limit }}</span> clients
                @if ($managerProfile->manager_code)
                    &nbsp;·&nbsp; Code: <span class="font-mono text-on-surface-variant">{{ $managerProfile->manager_code }}</span>
                @endif
            </p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium
            @if($managerProfile->status === 'active') bg-status-active/10 text-status-active border border-status-active/20
            @elseif($managerProfile->status === 'pending') bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20
            @elseif($managerProfile->status === 'suspended') bg-status-blocked/10 text-status-blocked border border-status-blocked/20
            @else bg-surface-container-low text-on-surface-variant border border-border-subtle
            @endif">
            {{ ucfirst($managerProfile->status) }}
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
                    <p class="text-sm font-semibold text-on-surface">My Workspaces</p>
                    <p class="text-xs text-outline mt-0.5">
                        {{ $myWorkspaces > 0 ? $myWorkspaces . ' active workspace' . ($myWorkspaces !== 1 ? 's' : '') : 'No active workspaces yet' }}
                    </p>
                </div>
            </div>
        </a>
        <a href="{{ route('workspace.index') }}"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">task_alt</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface">Task Board</p>
                    <p class="text-xs text-outline mt-0.5">
                        {{ $managerTasksOpen }} open &middot; {{ $managerTasksSubmitted }} awaiting review
                    </p>
                </div>
            </div>
        </a>
    </div>

    {{-- ── Task summary (Phase 5) ──────────────────────────────────────── --}}
    @if ($managedWorkspaceIds->isNotEmpty())
    <div class="mb-8">
        <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">Workspace Tasks</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <a href="{{ route('workspace.index') }}"
               class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-secondary">{{ $managerTasksOpen }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Open Tasks in My Workspaces</p>
            </a>
            <a href="{{ route('workspace.index') }}"
               class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-status-trial/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-status-trial">{{ $managerTasksSubmitted }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Submitted — Awaiting Review</p>
            </a>
        </div>
    </div>

    {{-- ── Workspace communication ──────────────────────────────────────── --}}
    <div class="mb-8">
        <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">Communication</h3>
        <p class="text-xs text-on-surface-variant mb-3">Open a workspace to access chat and files.</p>
        <a href="{{ route('workspace.index') }}"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm flex items-center gap-3">
            <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">forum</span>
            </div>
            <div>
                <p class="text-sm font-semibold text-on-surface">Chat &amp; Files</p>
                <p class="text-xs text-outline mt-0.5">Post messages, share files and manage task attachments</p>
            </div>
        </a>
    </div>
    @endif

    <div class="bg-secondary/5 border border-secondary/20 rounded-xl px-6 py-5 flex items-start gap-3">
        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size: 18px;">info</span>
        <div>
            <p class="text-sm font-semibold text-secondary">Phase 7 — Time Tracking &amp; Work Reports</p>
            <p class="text-sm text-on-surface-variant mt-0.5">
                Time logs and weekly reports are now live. Review and approve talent time submissions, then publish weekly reports for your clients.
            </p>
        </div>
    </div>

</x-layouts.gvos>
