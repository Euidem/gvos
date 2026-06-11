<x-layouts.gvos title="My Dashboard">
{{-- Stitch reference: client_dashboard_gvos/code.html (staff variant) --}}
@php
    $user = auth()->user();
    $profile = $user->profile;
    $clientProfile = $user->clientProfile;

    $clientWorkspaceIds = \App\Models\WorkspaceMember::where('user_id', $user->id)
        ->where('status', 'active')->pluck('workspace_id');

    $myWorkspaces         = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
        ->whereIn('status', ['pending', 'active'])->count();
    $clientOpenTasks      = \App\Models\WorkspaceTask::whereIn('workspace_id', $clientWorkspaceIds)
        ->whereIn('status', ['pending', 'in_progress', 'blocked'])->count();
    $clientSubmittedTasks = \App\Models\WorkspaceTask::whereIn('workspace_id', $clientWorkspaceIds)
        ->where('status', 'submitted')->count();
    $publishedReports     = \App\Models\WorkspaceWeeklyReport::whereIn('workspace_id', $clientWorkspaceIds)
        ->where('status', 'published')->count();

    $name = $profile?->first_name ?? $user->name ?? 'there';
@endphp

{{-- Phase 16: onboarding banner --}}
@php $__obUser = $user; @endphp
@include('partials.onboarding-banner')

{{-- ── Hero panel ───────────────────────────────────────────────────────── --}}
<div class="rounded-2xl border border-border-subtle shadow-sm overflow-hidden mb-8"
     style="background:linear-gradient(135deg,rgba(0,88,190,0.03) 0%,rgba(255,255,255,0) 55%),#fff;">
    <div class="p-6 lg:p-8">
        <p class="font-label-md text-label-md text-secondary uppercase tracking-widest mb-2">Your GVOS Workspace Access</p>
        <h2 class="font-headline-lg text-headline-lg text-on-surface">Welcome back, {{ $name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-2">
            @if ($myWorkspaces === 0)
                You have not been added to a workspace yet. Contact your business admin to get access.
            @elseif ($clientSubmittedTasks > 0)
                {{ $clientSubmittedTasks }} {{ Str::plural('task', $clientSubmittedTasks) }} pending approval across your {{ Str::plural('workspace', $myWorkspaces) }}.
            @else
                {{ $myWorkspaces }} active {{ Str::plural('workspace', $myWorkspaces) }}. View updates, files, tasks and reports shared with your team.
            @endif
        </p>
        @if ($myWorkspaces > 0)
            <div class="flex flex-wrap gap-3 mt-4">
                <a href="{{ route('workspace.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-secondary text-on-secondary rounded-lg font-label-md text-label-md hover:brightness-110 shadow-sm transition-all">
                    <span class="material-symbols-outlined" style="font-size:16px;">workspaces</span>
                    My Workspaces
                </a>
                <a href="{{ route('notifications.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg font-label-md text-label-md border border-border-subtle text-on-surface-variant hover:bg-surface-container-low hover:border-secondary/30 transition-all">
                    <span class="material-symbols-outlined" style="font-size:16px;">notifications</span>
                    Notifications
                </a>
            </div>
        @endif
    </div>
</div>

{{-- ── Access stats (4 cards) ──────────────────────────────────────────── --}}
<section class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <x-portal.stat-card
        label="Workspaces"
        :value="$myWorkspaces"
        icon="workspaces"
        accent="secondary"
        :href="route('workspace.index')"
        :hint="$myWorkspaces === 0 ? 'Access pending' : 'Active &amp; pending'" />

    <x-portal.stat-card
        label="Open Tasks"
        :value="$clientOpenTasks"
        icon="task_alt"
        accent="secondary"
        :hint="$clientOpenTasks === 0 ? 'No active tasks' : 'In progress'" />

    <x-portal.stat-card
        label="For Approval"
        :value="$clientSubmittedTasks"
        icon="pending_actions"
        accent="status-payment-due"
        :value-class="$clientSubmittedTasks > 0 ? 'text-status-payment-due' : 'text-primary'"
        :hint="$clientSubmittedTasks > 0 ? 'Awaiting review' : 'Nothing pending'" />

    <x-portal.stat-card
        label="Reports"
        :value="$publishedReports"
        icon="summarize"
        accent="status-active"
        :hint="$publishedReports === 0 ? 'First report pending' : 'Shared with your team'" />

</section>

{{-- ── Workspace list (with quick links per workspace) ───────────────────── --}}
<x-portal.section-card flush title="Your Workspaces" class="mb-6">
    <x-slot:actions>
        @if ($myWorkspaces > 0)
            <a href="{{ route('workspace.index') }}"
               class="text-secondary font-label-md text-label-md hover:underline flex items-center gap-1">
                View all
                <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
            </a>
        @endif
    </x-slot:actions>

    @if ($myWorkspaces === 0)
        <x-portal.empty-state
            compact
            icon="workspaces"
            title="No workspaces assigned yet"
            message="You have not been added to a workspace yet. Contact your business admin to request access." />
    @else
        @php
            $workspaceList = \App\Models\Workspace::whereIn('id', $clientWorkspaceIds)
                ->whereIn('status', ['pending', 'active'])
                ->with(['primaryTalent'])
                ->limit(5)
                ->get();
        @endphp
        <div class="divide-y divide-border-subtle">
            @foreach ($workspaceList as $ws)
                <div class="px-card-padding pt-4 pb-3">
                    {{-- Workspace identity --}}
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                             style="background-color:#0058be;">
                            {{ strtoupper(substr($ws->name, 0, 2)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('workspace.show', $ws) }}"
                               class="font-body-sm font-semibold text-on-surface hover:text-secondary transition-colors block truncate">
                                {{ $ws->name }}
                            </a>
                            <p class="font-label-md text-[10px] text-outline">
                                {{ $ws->workspace_code }}
                                @if ($ws->primaryTalent)
                                    &middot; {{ $ws->primaryTalent->name }}
                                @endif
                            </p>
                        </div>
                        <x-portal.status-badge :status="$ws->status" />
                    </div>
                    {{-- Quick-link chips --}}
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('workspace.tasks.index', $ws) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                           style="background:rgba(0,88,190,0.03);">
                            <span class="material-symbols-outlined" style="font-size:13px;">task_alt</span>
                            Tasks
                        </a>
                        <a href="{{ route('workspace.reports.index', $ws) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                           style="background:rgba(0,88,190,0.03);">
                            <span class="material-symbols-outlined" style="font-size:13px;">summarize</span>
                            Reports
                        </a>
                        <a href="{{ route('workspace.files.index', $ws) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                           style="background:rgba(0,88,190,0.03);">
                            <span class="material-symbols-outlined" style="font-size:13px;">folder_open</span>
                            Files
                        </a>
                        <a href="{{ route('workspace.chat.index', $ws) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/40 hover:text-secondary transition-all"
                           style="background:rgba(0,88,190,0.03);">
                            <span class="material-symbols-outlined" style="font-size:13px;">forum</span>
                            Messages
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-portal.section-card>

{{-- ── Quick action cards ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <x-portal.action-card
        :href="route('workspace.index')"
        icon="workspaces"
        title="All Workspaces"
        description="View every workspace your team has access to." />
    <x-portal.action-card
        :href="route('notifications.index')"
        icon="notifications"
        title="Notifications"
        description="Stay updated on team activity and workspace changes." />
    <x-portal.action-card
        :href="route('workspace.index')"
        icon="folder_open"
        title="Shared Files"
        description="Access documents and deliverables shared with your team. No files have been shared yet if this page is empty." />
    <x-portal.action-card
        :href="route('profile.show')"
        icon="person"
        title="My Profile"
        description="Update your personal details and preferences." />
</div>

</x-layouts.gvos>
