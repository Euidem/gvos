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

    // Task counts (Phase 5)
    $myAssignedTasks = \App\Models\WorkspaceTask::where('assigned_to_user_id', $user->id)
        ->whereIn('status', ['pending', 'in_progress', 'blocked', 'revision_requested'])
        ->count();
    $myDueSoonTasks = \App\Models\WorkspaceTask::where('assigned_to_user_id', $user->id)
        ->whereIn('status', ['pending', 'in_progress'])
        ->whereNotNull('due_date')
        ->whereDate('due_date', '<=', now()->addDays(3))
        ->count();
    $myBlockedTasks = \App\Models\WorkspaceTask::where('assigned_to_user_id', $user->id)
        ->where('status', 'blocked')
        ->count();
@endphp

    <div class="flex items-start justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-on-surface">
                Welcome back{{ $profile?->first_name ? ', ' . $profile->first_name : '' }}
            </h2>
            <p class="text-sm text-on-surface-variant mt-1">GVOS Talent Portal</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-xs bg-status-active/10 text-status-active border border-status-active/20 px-3 py-1 rounded-full font-medium">
                {{ ucfirst($user->status) }}
            </span>
            <span class="text-xs bg-status-active/10 text-status-active border border-status-active/20 px-3 py-1 rounded-full font-medium">
                Talent
            </span>
        </div>
    </div>

    {{-- ── Talent profile status card ───────────────────────────────────── --}}
    @if ($talentProfile)
    <div class="bg-white rounded-xl border border-border-subtle px-6 py-4 mb-6 flex items-center gap-4 shadow-card">
        <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">badge</span>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-on-surface">Talent Profile</p>
            <p class="text-xs text-outline mt-0.5">
                Training: <span class="font-medium text-on-surface-variant">{{ ucwords(str_replace('_', ' ', $talentProfile->training_status)) }}</span>
                &nbsp;·&nbsp;
                Equipment: <span class="font-medium text-on-surface-variant">{{ ucwords(str_replace('_', ' ', $talentProfile->equipment_status)) }}</span>
                @if ($talentProfile->talent_code)
                    &nbsp;·&nbsp; Code: <span class="font-mono text-on-surface-variant">{{ $talentProfile->talent_code }}</span>
                @endif
            </p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium
            @if($talentProfile->status === 'active') bg-status-active/10 text-status-active border border-status-active/20
            @elseif($talentProfile->status === 'pending') bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20
            @elseif($talentProfile->status === 'suspended') bg-status-blocked/10 text-status-blocked border border-status-blocked/20
            @else bg-surface-container-low text-on-surface-variant border border-border-subtle
            @endif">
            {{ ucfirst($talentProfile->status) }}
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
        <div class="bg-white rounded-xl border border-dashed border-border-subtle px-5 py-4 opacity-50 cursor-not-allowed shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-surface-container-low rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-outline" style="font-size: 18px;">timer</span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-on-surface-variant">Time Tracker</p>
                    <p class="text-xs text-outline mt-0.5">Coming in a later phase</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Workspace communication ──────────────────────────────────────── --}}
    @if ($myWorkspaces > 0)
    <div class="mb-8">
        <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">Workspace Communication</h3>
        <p class="text-xs text-on-surface-variant mb-3">Open a workspace to access chat and files.</p>
        <a href="{{ route('workspace.index') }}"
           class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm flex items-center gap-3">
            <div class="w-9 h-9 bg-secondary/5 rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">forum</span>
            </div>
            <div>
                <p class="text-sm font-semibold text-on-surface">Chat &amp; Files</p>
                <p class="text-xs text-outline mt-0.5">View messages and shared files in your workspaces</p>
            </div>
        </a>
    </div>
    @endif

    {{-- ── My Tasks (Phase 5) ───────────────────────────────────────────── --}}
    @if ($myAssignedTasks > 0 || $myBlockedTasks > 0)
    <div class="mb-8">
        <h3 class="text-xs font-semibold text-outline mb-3 uppercase tracking-wider">My Tasks</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <a href="{{ route('workspace.index') }}"
               class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-secondary/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-secondary">{{ $myAssignedTasks }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Active Tasks</p>
            </a>
            @if ($myBlockedTasks > 0)
            <a href="{{ route('workspace.index') }}"
               class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-status-blocked/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-status-blocked">{{ $myBlockedTasks }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Blocked</p>
            </a>
            @endif
            @if ($myDueSoonTasks > 0)
            <a href="{{ route('workspace.index') }}"
               class="bg-white rounded-xl border border-border-subtle px-5 py-4 hover:border-status-payment-due/30 hover:shadow-card transition-all shadow-sm">
                <p class="text-2xl font-bold text-status-payment-due">{{ $myDueSoonTasks }}</p>
                <p class="text-xs text-on-surface-variant mt-1 font-medium">Due Soon</p>
            </a>
            @endif
        </div>
    </div>
    @endif

    <div class="bg-secondary/5 border border-secondary/20 rounded-xl px-6 py-5 flex items-start gap-3">
        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size: 18px;">info</span>
        <div>
            <p class="text-sm font-semibold text-secondary">Phase 6 — Chat &amp; Files</p>
            <p class="text-sm text-on-surface-variant mt-0.5">
                Workspace chat and file sharing are now live. Open your workspace to view messages, share files and access task attachments.
            </p>
        </div>
    </div>

</x-layouts.gvos>
