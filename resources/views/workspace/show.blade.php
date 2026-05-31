<x-layouts.gvos :title="$workspace->name">

    <div class="max-w-4xl mx-auto space-y-8">

        @php
            $user           = auth()->user();
            $userIsAdmin    = $user->hasAnyRole(['super_admin', 'operations_admin']);
            $activeMember   = $workspace->activeMembers->firstWhere('user_id', $user->id);
            $memberRole     = $activeMember?->role;
            $userIsPrimary  = in_array($user->id, [$workspace->primary_manager_id, $workspace->primary_talent_id]);
            $effectiveRole  = $userIsAdmin ? 'admin'
                            : ($memberRole ?? ($userIsPrimary ? ($workspace->primary_manager_id === $user->id ? 'manager' : 'talent') : 'none'));
            $canCreateTask  = $effectiveRole !== 'none' && $effectiveRole !== 'observer';

            // Task counts for summary
            $taskCounts = $workspace->tasks()->selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
            $openCount  = $taskCounts->only(['pending','in_progress','blocked','submitted','revision_requested'])->sum();
            $totalCount = $taskCounts->sum();

            // Phase 7 counts
            $timeLogCount  = $workspace->timeLogs()->count();
            $reportCount   = $workspace->weeklyReports()->count();
            // Clients see only approved+client_summary logs, and published reports
            if (in_array($effectiveRole, ['client_admin','client_staff','client'], true)) {
                $timeLogCount = $workspace->timeLogs()->where('status','approved')->where('visibility','client_summary')->count();
                $reportCount  = $workspace->weeklyReports()->where('status','published')->count();
            }

            $statusColors = [
                'active'    => 'bg-status-active/10 text-status-active border border-status-active/20',
                'pending'   => 'bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20',
                'paused'    => 'bg-secondary/5 text-secondary border border-secondary/20',
                'completed' => 'bg-status-completed/10 text-status-completed border border-status-completed/20',
                'cancelled' => 'bg-status-blocked/10 text-status-blocked border border-status-blocked/20',
            ];
            $statusCls = $statusColors[$workspace->status] ?? 'bg-surface-container-low text-on-surface-variant border border-border-subtle';
        @endphp

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-2xl font-bold text-on-surface">{{ $workspace->name }}</h2>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusCls }}">
                        {{ ucfirst($workspace->status) }}
                    </span>
                </div>
                <p class="text-sm text-on-surface-variant">{{ $workspace->workspace_code }} &middot; {{ ucfirst($workspace->type) }} workspace</p>
            </div>
            <a href="{{ route('workspace.index') }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1 mt-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                All Workspaces
            </a>
        </div>

        {{-- ── Status banner ────────────────────────────────────────────── --}}
        @if ($workspace->status === 'pending')
            <div class="bg-status-payment-due/10 border border-status-payment-due/20 rounded-xl px-5 py-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-status-payment-due flex-shrink-0 mt-0.5" style="font-size: 20px;">schedule</span>
                <div>
                    <p class="text-sm font-semibold text-status-payment-due">Workspace pending activation</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">The GVOS team will activate this workspace when your engagement begins.</p>
                </div>
            </div>
        @elseif ($workspace->status === 'active')
            <div class="bg-status-active/10 border border-status-active/20 rounded-xl px-5 py-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-status-active flex-shrink-0 mt-0.5" style="font-size: 20px;">check_circle</span>
                <div>
                    <p class="text-sm font-semibold text-status-active">Workspace is active</p>
                    @if ($workspace->ends_at)
                        <p class="text-xs text-on-surface-variant mt-0.5">
                            Ends {{ $workspace->ends_at->format('d M Y \a\t H:i') }}
                            @php $hoursLeft = max(0, now()->floatDiffInHours($workspace->ends_at, false)); @endphp
                            &mdash; {{ number_format($hoursLeft, 1) }} hours remaining.
                        </p>
                    @endif
                </div>
            </div>
        @elseif ($workspace->status === 'completed')
            <div class="bg-status-completed/10 border border-status-completed/20 rounded-xl px-5 py-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-status-completed flex-shrink-0 mt-0.5" style="font-size: 20px;">task_alt</span>
                <div>
                    <p class="text-sm font-semibold text-status-completed">Workspace completed</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">This workspace has concluded. Contact the GVOS team to discuss next steps.</p>
                </div>
            </div>
        @endif

        {{-- ── Details grid ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Team card --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
                <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">group</span>
                    Your Team
                </h3>
                <div class="space-y-3">
                    @if ($workspace->primaryManager)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-secondary/10 rounded-full flex items-center justify-center text-secondary font-bold text-xs">
                                {{ strtoupper(substr($workspace->primaryManager->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-on-surface">{{ $workspace->primaryManager->name }}</p>
                                <p class="text-xs text-outline">Manager</p>
                            </div>
                        </div>
                    @endif
                    @if ($workspace->primaryTalent)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-status-active/10 rounded-full flex items-center justify-center text-status-active font-bold text-xs">
                                {{ strtoupper(substr($workspace->primaryTalent->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-on-surface">{{ $workspace->primaryTalent->name }}</p>
                                <p class="text-xs text-outline">Talent</p>
                            </div>
                        </div>
                    @endif
                    @if (! $workspace->primaryManager && ! $workspace->primaryTalent)
                        <p class="text-sm text-outline italic">Team to be assigned</p>
                    @endif
                </div>
            </div>

            {{-- Schedule card --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
                <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">calendar_today</span>
                    Schedule
                </h3>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Status</span>
                        <span class="font-semibold text-on-surface capitalize">{{ $workspace->status }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Type</span>
                        <span class="font-semibold text-on-surface capitalize">{{ $workspace->type }}</span>
                    </div>
                    @if ($workspace->starts_at)
                        <div class="flex justify-between">
                            <span class="text-on-surface-variant">Started</span>
                            <span class="font-semibold text-on-surface">{{ $workspace->starts_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    @if ($workspace->ends_at)
                        <div class="flex justify-between">
                            <span class="text-on-surface-variant">Ends</span>
                            <span class="font-semibold text-on-surface">{{ $workspace->ends_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    @if ($workspace->task_limit > 0)
                        <div class="flex justify-between">
                            <span class="text-on-surface-variant">Task limit</span>
                            <span class="font-semibold text-on-surface">{{ $workspace->task_limit }}</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Members ───────────────────────────────────────────────────── --}}
        @if ($workspace->activeMembers->isNotEmpty())
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
                <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">people</span>
                    All Workspace Members
                </h3>
                <div class="divide-y divide-border-subtle">
                    @foreach ($workspace->activeMembers as $member)
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-surface-container-low rounded-full flex items-center justify-center text-on-surface-variant font-bold text-xs">
                                    {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-on-surface">{{ $member->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-outline">{{ $member->user->email ?? '' }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ match($member->role) {
                                    'manager' => 'bg-secondary/5 text-secondary border border-secondary/20',
                                    'talent'  => 'bg-status-active/10 text-status-active border border-status-active/20',
                                    'client'  => 'bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20',
                                    default   => 'bg-surface-container-low text-on-surface-variant border border-border-subtle',
                                } }}">
                                {{ ucfirst($member->role) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Kanban Board Summary ─────────────────────────────────────────── --}}
        @php
            $blockedCount   = $taskCounts['blocked']   ?? 0;
            $submittedCount = $taskCounts['submitted']  ?? 0;
        @endphp
        <div class="bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden">

            {{-- Section header --}}
            <div class="px-6 pt-5 pb-4 flex items-center justify-between border-b border-border-subtle">
                <h3 class="text-sm font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">view_kanban</span>
                    Kanban Board
                </h3>
                <div class="flex items-center gap-2">
                    @if ($canCreateTask)
                        <a href="{{ route('workspace.tasks.create', $workspace) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
                           style="border-color:#0058be; color:#0058be;">
                            <span class="material-symbols-outlined" style="font-size: 14px;">add</span>
                            New Task
                        </a>
                    @endif
                    <a href="{{ route('workspace.tasks.index', $workspace) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition-all hover:brightness-110"
                       style="background-color:#0058be">
                        <span class="material-symbols-outlined" style="font-size: 14px;">view_kanban</span>
                        Open Kanban Board
                    </a>
                </div>
            </div>

            @if ($totalCount === 0)
                {{-- Empty state --}}
                <div class="p-8 text-center">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center mx-auto mb-3"
                         style="background-color:rgba(0,88,190,.06);">
                        <span class="material-symbols-outlined" style="font-size:22px; color:#0058be;">view_kanban</span>
                    </div>
                    <p class="text-sm text-outline italic">No tasks yet in this workspace.</p>
                    @if ($canCreateTask)
                        <a href="{{ route('workspace.tasks.create', $workspace) }}"
                           class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 rounded-lg text-xs font-semibold text-white"
                           style="background-color:#0058be">
                            <span class="material-symbols-outlined" style="font-size: 14px;">add</span>
                            Create First Task
                        </a>
                    @endif
                </div>

            @else
                {{-- Task metric cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-0 border-b border-border-subtle divide-x divide-border-subtle">
                    <div class="px-5 py-4 text-center">
                        <p class="text-2xl font-bold text-on-surface">{{ $totalCount }}</p>
                        <p class="text-xs text-outline mt-0.5 font-medium">Total</p>
                    </div>
                    <div class="px-5 py-4 text-center">
                        <p class="text-2xl font-bold" style="color:#0058be;">{{ $openCount }}</p>
                        <p class="text-xs text-outline mt-0.5 font-medium">Open</p>
                    </div>
                    <div class="px-5 py-4 text-center">
                        <p class="text-2xl font-bold" style="color:#DC2626;">{{ $blockedCount }}</p>
                        <p class="text-xs text-outline mt-0.5 font-medium">Blocked</p>
                    </div>
                    <div class="px-5 py-4 text-center">
                        <p class="text-2xl font-bold" style="color:#7C3AED;">{{ $submittedCount }}</p>
                        <p class="text-xs text-outline mt-0.5 font-medium">Awaiting Review</p>
                    </div>
                </div>

                {{-- Status chips --}}
                <div class="px-6 py-4 flex flex-wrap gap-2 border-b border-border-subtle">
                    @php
                        $chipDefs = [
                            'pending'            => ['label' => 'Pending',        'bg' => '#FFFBEB', 'color' => '#D97706'],
                            'in_progress'        => ['label' => 'In Progress',    'bg' => '#EFF6FF', 'color' => '#0058be'],
                            'blocked'            => ['label' => 'Blocked',        'bg' => '#FFF5F5', 'color' => '#DC2626'],
                            'submitted'          => ['label' => 'Submitted',      'bg' => '#F5F3FF', 'color' => '#7C3AED'],
                            'revision_requested' => ['label' => 'Revision Req.',  'bg' => '#FFF7ED', 'color' => '#EA580C'],
                            'approved'           => ['label' => 'Approved',       'bg' => '#F0FDF4', 'color' => '#059669'],
                            'closed'             => ['label' => 'Closed',         'bg' => '#F9FAFB', 'color' => '#6B7280'],
                        ];
                    @endphp
                    @foreach ($chipDefs as $s => $chip)
                        @if (($taskCounts[$s] ?? 0) > 0)
                            <a href="{{ route('workspace.tasks.index', $workspace) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold hover:opacity-80 transition-opacity"
                               style="background-color:{{ $chip['bg'] }}; color:{{ $chip['color'] }}; border:1px solid {{ $chip['color'] }}33;">
                                {{ $taskCounts[$s] }} {{ $chip['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>

                {{-- Recent open tasks preview --}}
                @php
                    $previewTasks = $workspace->tasks()
                        ->with(['assignedTo'])
                        ->whereIn('status', ['pending','in_progress','blocked','submitted','revision_requested'])
                        ->orderBy('created_at', 'desc')
                        ->limit(4)
                        ->get();
                @endphp
                @if ($previewTasks->isNotEmpty())
                    <div class="divide-y divide-border-subtle">
                        @foreach ($previewTasks as $pt)
                            <a href="{{ route('workspace.tasks.show', [$workspace, $pt]) }}"
                               class="flex items-center justify-between px-6 py-3 hover:bg-surface-container-low transition-colors group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="text-[10px] font-mono text-outline flex-shrink-0">{{ $pt->task_code }}</span>
                                    <span class="text-sm text-on-surface truncate group-hover:text-secondary transition-colors">{{ $pt->title }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0 ml-3">
                                    @if ($pt->assignedTo)
                                        <span class="text-xs text-outline hidden sm:block">{{ Str::limit($pt->assignedTo->name, 12) }}</span>
                                    @endif
                                    @if ($pt->due_date)
                                        <span class="text-[11px] font-medium" style="color: {{ $pt->isOverdue() ? '#DC2626' : '#9CA3AF' }};">
                                            {{ $pt->due_date->format('d M') }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @if ($openCount > 4)
                        <div class="px-6 py-3 text-center border-t border-border-subtle">
                            <a href="{{ route('workspace.tasks.index', $workspace) }}"
                               class="text-xs font-semibold transition-all hover:brightness-110"
                               style="color:#0058be;">
                                View all {{ $openCount }} open tasks on Kanban Board →
                            </a>
                        </div>
                    @endif
                @endif
            @endif
        </div>

        {{-- ── Communication & Files section ─────────────────────────────── --}}
        @php
            $msgCount  = $workspace->messages()->where('visibility', 'public')->count();
            $fileCount = $workspace->files()->where('visibility', 'public')->count();
            if ($canCreateTask) {
                // admin/manager/workspace_admin/talent also see internal counts
                if (in_array($effectiveRole, ['admin', 'manager', 'workspace_admin'], true)) {
                    $msgCount  = $workspace->messages()->count();
                    $fileCount = $workspace->files()->count();
                }
            }
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Chat card --}}
            <a href="{{ route('workspace.chat.index', $workspace) }}"
               class="bg-white rounded-xl border border-border-subtle shadow-card p-6 hover:border-secondary/30 hover:shadow-card transition-all group">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                             style="background-color:rgba(0,88,190,.06);">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">forum</span>
                        </div>
                        <h3 class="text-sm font-bold text-on-surface">Workspace Chat</h3>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                          style="background:rgba(0,88,190,.06);color:#0058be;">
                        {{ $msgCount }} {{ Str::plural('message', $msgCount) }}
                    </span>
                </div>
                <p class="text-xs text-outline leading-relaxed">
                    Post messages, share updates, and communicate with the workspace team.
                </p>
                <p class="text-xs font-semibold mt-3 group-hover:underline transition-all" style="color:#0058be;">
                    Open Chat →
                </p>
            </a>

            {{-- Files card --}}
            <a href="{{ route('workspace.files.index', $workspace) }}"
               class="bg-white rounded-xl border border-border-subtle shadow-card p-6 hover:border-secondary/30 hover:shadow-card transition-all group">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                             style="background-color:rgba(0,88,190,.06);">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">folder_open</span>
                        </div>
                        <h3 class="text-sm font-bold text-on-surface">File Library</h3>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                          style="background:rgba(0,88,190,.06);color:#0058be;">
                        {{ $fileCount }} {{ Str::plural('file', $fileCount) }}
                    </span>
                </div>
                <p class="text-xs text-outline leading-relaxed">
                    Upload and access shared files, briefs, deliverables and task attachments.
                </p>
                <p class="text-xs font-semibold mt-3 group-hover:underline transition-all" style="color:#0058be;">
                    Open Files →
                </p>
            </a>
        </div>

        {{-- ── Time Tracking & Reports (Phase 7 — active) ─────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Time Logs card --}}
            <a href="{{ route('workspace.time-logs.index', $workspace) }}"
               class="bg-white rounded-xl border border-border-subtle shadow-card p-6 hover:border-secondary/30 hover:shadow-card transition-all group">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                             style="background-color:rgba(0,88,190,.06);">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">schedule</span>
                        </div>
                        <h3 class="text-sm font-bold text-on-surface">Time Logs</h3>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                          style="background:rgba(0,88,190,.06);color:#0058be;">
                        {{ $timeLogCount }} {{ Str::plural('log', $timeLogCount) }}
                    </span>
                </div>
                <p class="text-xs text-outline leading-relaxed">
                    Track and review work sessions, durations and daily activity logs.
                </p>
                <p class="text-xs font-semibold mt-3 group-hover:underline transition-all" style="color:#0058be;">
                    View Time Logs →
                </p>
            </a>

            {{-- Weekly Reports card --}}
            <a href="{{ route('workspace.reports.index', $workspace) }}"
               class="bg-white rounded-xl border border-border-subtle shadow-card p-6 hover:border-secondary/30 hover:shadow-card transition-all group">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                             style="background-color:rgba(0,88,190,.06);">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">summarize</span>
                        </div>
                        <h3 class="text-sm font-bold text-on-surface">Weekly Reports</h3>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
                          style="background:rgba(0,88,190,.06);color:#0058be;">
                        {{ $reportCount }} {{ Str::plural('report', $reportCount) }}
                    </span>
                </div>
                <p class="text-xs text-outline leading-relaxed">
                    View weekly work summaries and progress reports for this workspace.
                </p>
                <p class="text-xs font-semibold mt-3 group-hover:underline transition-all" style="color:#0058be;">
                    View Reports →
                </p>
            </a>
        </div>

        {{-- ── Future sections (placeholders) ────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-dashed border-border-subtle p-5 opacity-50 cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-surface-container-low rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-outline" style="font-size: 18px;">receipt</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-on-surface-variant">Billing</p>
                        <p class="text-xs text-outline mt-0.5">Coming in a later phase</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-dashed border-border-subtle p-5 opacity-50 cursor-not-allowed">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-surface-container-low rounded-lg flex items-center justify-center">
                        <span class="material-symbols-outlined text-outline" style="font-size: 18px;">lock</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-on-surface-variant">Password Vault</p>
                        <p class="text-xs text-outline mt-0.5">Coming in a later phase</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Back link ─────────────────────────────────────────────────── --}}
        <div class="text-center pb-4">
            <a href="{{ route('workspace.index') }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center justify-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Back to Workspaces
            </a>
        </div>

    </div>

</x-layouts.gvos>
