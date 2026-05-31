<x-layouts.gvos title="My Dashboard">
{{-- Stitch reference: talent_dashboard_gvos_1/code.html --}}
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

    $myTimeLogs = \App\Models\WorkspaceTimeLog::where('user_id', $user->id);
    $weekStart  = now()->startOfWeek();
    $timeThisWeek = (clone $myTimeLogs)
        ->where('status', 'approved')
        ->whereBetween('log_date', [$weekStart->format('Y-m-d'), now()->format('Y-m-d')])
        ->sum('duration_minutes');
    $timeThisWeekH = intdiv($timeThisWeek, 60);

    $name = $profile?->first_name ?? $user->name ?? 'there';
@endphp

{{-- ── Page header + Clock-In placeholder ─────────────────────────────── --}}
{{-- Stitch: flex row with welcome left + Clock-In/Out widget right --}}
<section class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
    <div>
        <h2 class="font-headline-lg text-headline-lg text-primary">Welcome back, {{ $name }}</h2>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            @if ($myAssignedTasks > 0)
                You have {{ $myAssignedTasks }} active {{ Str::plural('task', $myAssignedTasks) }}
                across {{ $myWorkspaces }} {{ Str::plural('workspace', $myWorkspaces) }}.
            @else
                Your workspaces are ready. Start your session when you're ready.
            @endif
        </p>
    </div>

    {{-- Clock-In/Out placeholder widget (UI only — timer logic comes in a later phase) --}}
    {{-- Stitch: bg-white p-2 pl-4 rounded-2xl border-2 border-border-subtle shadow-lg --}}
    <div class="bg-white p-2 pl-4 rounded-2xl border-2 border-border-subtle shadow-lg flex items-center gap-8 min-w-fit">
        <div class="flex items-center gap-4">
            <div class="relative flex items-center justify-center">
                <div class="w-12 h-12 rounded-full flex items-center justify-center"
                     style="background:rgba(16,185,129,0.1);color:#10B981;">
                    <span class="material-symbols-outlined text-2xl"
                          style="font-variation-settings: 'FILL' 1;">timer</span>
                </div>
            </div>
            <div>
                <p class="font-label-md text-[10px] text-outline uppercase tracking-widest">
                    {{ $timeThisWeek > 0 ? 'This week' : 'Session' }}
                </p>
                <p class="font-mono-sm text-lg font-bold text-primary tracking-tight">
                    {{ $timeThisWeek > 0 ? $timeThisWeekH . 'h logged' : 'Not started' }}
                </p>
            </div>
        </div>
        @if ($myWorkspaces > 0)
            <a href="{{ route('workspace.index') }}"
               class="bg-secondary text-on-secondary px-6 py-3 rounded-xl font-bold font-label-md text-label-md
                      hover:brightness-110 transition-all active:scale-95 shadow-md"
               style="box-shadow:0 4px 12px rgba(0,88,190,0.2);">
                Log Time
            </a>
        @else
            <span class="bg-surface-container text-outline px-6 py-3 rounded-xl font-bold font-label-md text-label-md cursor-not-allowed">
                Log Time
            </span>
        @endif
    </div>
</section>

{{-- ── Metrics bento grid (4 columns) ─────────────────────────────────── --}}
{{-- Stitch: 4-col grid — Today's Tasks, Overdue, Blocked, Weekly Goal --}}
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between
                group hover:border-secondary transition-all hover:shadow-md cursor-pointer">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline uppercase tracking-wider">Active Tasks</span>
            <span class="material-symbols-outlined text-secondary opacity-0 group-hover:opacity-100 transition-opacity"
                  style="font-size:18px;">task_alt</span>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
            <span class="font-headline-lg text-headline-lg text-primary">{{ $myAssignedTasks }}</span>
            @if ($myDueSoonTasks > 0)
                <span class="font-label-md text-label-md text-status-payment-due">{{ $myDueSoonTasks }} due soon</span>
            @endif
        </div>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between
                group hover:border-status-urgent transition-all hover:shadow-md cursor-pointer">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline uppercase tracking-wider">Due Soon</span>
            <span class="material-symbols-outlined text-status-urgent opacity-0 group-hover:opacity-100 transition-opacity"
                  style="font-size:18px;">schedule</span>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
            <span class="font-headline-lg text-headline-lg {{ $myDueSoonTasks > 0 ? 'text-status-urgent' : 'text-primary' }}">
                {{ $myDueSoonTasks }}
            </span>
            <span class="font-label-md text-label-md text-outline">
                {{ $myDueSoonTasks > 0 ? 'within 3 days' : 'No urgent items' }}
            </span>
        </div>
    </div>

    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between
                group hover:border-status-blocked transition-all hover:shadow-md cursor-pointer">
        <div class="flex justify-between items-start">
            <span class="font-label-md text-label-md text-outline uppercase tracking-wider">Blocked</span>
            <span class="material-symbols-outlined text-status-blocked opacity-0 group-hover:opacity-100 transition-opacity"
                  style="font-size:18px;">block</span>
        </div>
        <div class="mt-4 flex items-baseline gap-2">
            <span class="font-headline-lg text-headline-lg {{ $myBlockedTasks > 0 ? 'text-status-blocked' : 'text-primary' }}">
                {{ $myBlockedTasks }}
            </span>
            <span class="font-label-md text-label-md text-outline">
                {{ $myBlockedTasks > 0 ? 'Awaiting feedback' : 'No blockers' }}
            </span>
        </div>
    </div>

    {{-- Weekly goal card with progress bar --}}
    <div class="bg-white p-card-padding rounded-xl border border-border-subtle shadow-sm flex flex-col justify-between">
        <div class="flex justify-between items-center mb-2">
            <span class="font-label-md text-label-md text-outline uppercase tracking-wider">Weekly Time</span>
            <span class="font-bold text-sm text-primary">{{ $timeThisWeekH }}h</span>
        </div>
        <div class="w-full bg-surface-container-high h-2.5 rounded-full overflow-hidden">
            @php $goalPct = min(100, $timeThisWeek > 0 ? round($timeThisWeek / (40 * 60) * 100) : 0); @endphp
            <div class="bg-secondary h-full rounded-full transition-all" style="width: {{ $goalPct }}%"></div>
        </div>
        <p class="font-label-md text-[10px] text-outline mt-3 flex items-center justify-between">
            <span>{{ $timeThisWeekH }}h / 40h goal</span>
            <span class="text-secondary font-bold">{{ 40 - $timeThisWeekH > 0 ? (40 - $timeThisWeekH) . 'h left' : 'Goal met!' }}</span>
        </p>
    </div>
</section>

{{-- ── Main content: My workspaces + Quick links ──────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- My Workspaces --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-card-padding py-4 border-b border-border-subtle flex items-center justify-between">
                <h3 class="font-headline-md text-headline-md text-primary font-bold">My Workspaces</h3>
                <a href="{{ route('workspace.index') }}"
                   class="text-secondary font-label-md text-label-md hover:underline font-bold">
                    View All
                </a>
            </div>

            @php
                $workspaceList = \App\Models\Workspace::where(function ($q) use ($user) {
                        $q->where('primary_talent_id', $user->id)
                          ->orWhereHas('members', fn ($m) => $m->where('user_id', $user->id)->where('status', 'active'));
                    })
                    ->whereIn('status', ['pending', 'active'])
                    ->with(['primaryManager'])
                    ->latest()
                    ->limit(5)
                    ->get();
            @endphp

            @if ($workspaceList->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-3"
                         style="background:rgba(0,88,190,0.06)">
                        <span class="material-symbols-outlined text-secondary" style="font-size:24px;">workspaces</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-outline">No active workspaces yet.</p>
                    <p class="font-label-md text-label-md text-on-surface-variant mt-1">
                        Your workspace will appear here once it's activated.
                    </p>
                </div>
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($workspaceList as $ws)
                        <a href="{{ route('workspace.show', $ws) }}"
                           class="flex items-center justify-between px-card-padding py-4 hover:bg-surface-container-low transition-all group">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-xs text-white flex-shrink-0"
                                     style="background-color:#0058be;">
                                    {{ strtoupper(substr($ws->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-body-sm font-semibold text-on-surface group-hover:text-secondary transition-colors">
                                        {{ $ws->name }}
                                    </p>
                                    <p class="font-label-md text-[10px] text-outline">
                                        {{ $ws->workspace_code }}
                                        @if ($ws->primaryManager)
                                            &middot; Mgr: {{ $ws->primaryManager->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @php
                                    $statusColor = match($ws->status) {
                                        'active'  => '#10B981',
                                        'pending' => '#F59E0B',
                                        default   => '#94A3B8',
                                    };
                                @endphp
                                <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                      style="color:{{ $statusColor }};background:{{ $statusColor }}18;">
                                    {{ ucfirst($ws->status) }}
                                </span>
                                <span class="material-symbols-outlined text-outline group-hover:text-secondary transition-colors"
                                      style="font-size:16px;">chevron_right</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Quick links sidebar --}}
    <div class="space-y-4">

        {{-- Talent profile status --}}
        @if ($talentProfile)
        <div class="bg-white rounded-xl border border-border-subtle p-card-padding shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                     style="background:rgba(0,88,190,0.06)">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">badge</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface font-semibold">Talent Profile</p>
                    @if ($talentProfile->talent_code)
                        <p class="font-mono-sm text-mono-sm text-outline">{{ $talentProfile->talent_code }}</p>
                    @endif
                </div>
            </div>
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-outline font-label-md text-label-md">Training</span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold">
                        {{ ucwords(str_replace('_', ' ', $talentProfile->training_status)) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-outline font-label-md text-label-md">Equipment</span>
                    <span class="font-label-md text-label-md text-on-surface font-semibold">
                        {{ ucwords(str_replace('_', ' ', $talentProfile->equipment_status)) }}
                    </span>
                </div>
            </div>
        </div>
        @endif

        {{-- Quick navigation --}}
        <div class="bg-white rounded-xl border border-border-subtle p-card-padding shadow-sm">
            <h4 class="font-label-md text-label-md text-outline uppercase tracking-wider mb-4">Quick Links</h4>
            <div class="space-y-1">
                @foreach ([
                    ['label' => 'My Workspaces',  'icon' => 'workspaces',  'route' => route('workspace.index')],
                    ['label' => 'Time Logs',       'icon' => 'schedule',    'route' => route('workspace.index')],
                    ['label' => 'My Profile',      'icon' => 'person',      'route' => route('profile.show')],
                ] as $link)
                <a href="{{ $link['route'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-on-surface-variant
                          hover:text-secondary hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined" style="font-size:18px;">{{ $link['icon'] }}</span>
                    <span class="font-label-md text-label-md">{{ $link['label'] }}</span>
                    <span class="material-symbols-outlined ml-auto" style="font-size:14px;color:#CBD5E1;">chevron_right</span>
                </a>
                @endforeach
            </div>
        </div>

    </div>
</div>

</x-layouts.gvos>
