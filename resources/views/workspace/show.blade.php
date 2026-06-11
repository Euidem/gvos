<x-layouts.gvos :title="$workspace->name">
{{-- Stitch reference: workspace_monitoring_gvos/code.html — Phase 26 Batch 3 --}}

    @php
        $user           = auth()->user();
        $userIsAdmin    = $user->hasAnyRole(['super_admin', 'operations_admin']);
        $activeMember   = $workspace->activeMembers->firstWhere('user_id', $user->id);
        $memberRole     = $activeMember?->role;
        $userIsPrimary  = in_array($user->id, [$workspace->primary_manager_id, $workspace->primary_talent_id]);
        $effectiveRole  = $workspace->resolveUserWorkspaceRole($user);
        $canCreateTask  = $workspace->userCanCreateTasks($user);
        $canViewMembers = in_array($effectiveRole, ['admin', 'workspace_admin', 'manager', 'client_admin', 'client_staff', 'talent', 'assigned_user'], true);
        $canManageMembers = in_array($effectiveRole, ['admin', 'workspace_admin', 'client_admin'], true);

        $teamActiveCount = $workspace->activeMembers->count();
        $teamManagerCount = $workspace->activeMembers->whereIn('role', ['manager', 'workspace_admin'])->count()
            + ($workspace->primaryManager ? 1 : 0);
        $teamTalentCount = $workspace->activeMembers->where('role', 'talent')->count()
            + ($workspace->primaryTalent ? 1 : 0);
        $teamClientCount = $workspace->activeMembers->whereIn('role', ['client_admin', 'client_staff', 'client'])->count();

        $taskCounts = $workspace->tasks()->selectRaw('status, count(*) as cnt')->groupBy('status')->pluck('cnt', 'status');
        $openCount  = $taskCounts->only(['pending','in_progress','blocked','submitted','revision_requested'])->sum();
        $totalCount = $taskCounts->sum();

        $timeLogCount  = $workspace->timeLogs()->count();
        $reportCount   = $workspace->weeklyReports()->count();
        if (in_array($effectiveRole, ['client_admin','client_staff','client'], true)) {
            $timeLogCount = $workspace->timeLogs()->where('status','approved')->where('visibility','client_summary')->count();
            $reportCount  = $workspace->weeklyReports()->where('status','published')->count();
        }

        $isClientRole   = in_array($effectiveRole, ['client_admin','client_staff','client'], true);
        $isManagerRole  = in_array($effectiveRole, ['admin','workspace_admin','manager'], true);
        $latestReport   = $workspace->weeklyReports()
            ->when($isClientRole, fn($q) => $q->where('status','published'))
            ->orderBy('week_start_date', 'desc')
            ->first();
        $reportsDraftCount = $isManagerRole
            ? $workspace->weeklyReports()->whereIn('status', ['draft','submitted'])->count()
            : 0;

        $subscription       = $workspace->activeSubscription;
        $outstandingBalance = $workspace->invoices()
            ->whereIn('status', ['issued', 'partially_paid', 'overdue'])
            ->sum('balance_due');
        $invoiceCount       = $workspace->invoices()->whereNotIn('status', ['void', 'cancelled'])->count();
        $canSeeBilling = ! in_array($effectiveRole, ['talent', 'assigned_user', 'observer', 'none'], true);

        $vaultRole      = $workspace->resolveUserWorkspaceRole($user);
        $canCreateVault = \App\Models\WorkspaceVaultItem::canCreateForRole($vaultRole);
        $vaultItemCount = \App\Models\WorkspaceVaultItem::queryForUser($workspace, $user, $vaultRole)
            ->active()
            ->count();
        $canSeeVault = \App\Models\WorkspaceVaultItem::canUseVaultRole($vaultRole)
            && ($canCreateVault || $vaultItemCount > 0);

        $statusColors = [
            'active'    => ['bg' => '#ECFDF5', 'border' => '#10B981', 'text' => '#065F46', 'dot' => '#10B981'],
            'pending'   => ['bg' => '#FFFBEB', 'border' => '#F59E0B', 'text' => '#92400E', 'dot' => '#F59E0B'],
            'paused'    => ['bg' => '#F1F5F9', 'border' => '#64748B', 'text' => '#475569', 'dot' => '#64748B'],
            'completed' => ['bg' => '#F0FDF4', 'border' => '#059669', 'text' => '#064E3B', 'dot' => '#059669'],
            'cancelled' => ['bg' => '#FEF2F2', 'border' => '#EF4444', 'text' => '#991B1B', 'dot' => '#EF4444'],
        ];
        $sc = $statusColors[$workspace->status] ?? ['bg' => '#F8FAFC', 'border' => '#94A3B8', 'text' => '#475569', 'dot' => '#94A3B8'];

        $msgCount  = $workspace->messages()->where('visibility', 'public')->count();
        $fileCount = $workspace->files()->where('visibility', 'public')->count();
        if ($canCreateTask) {
            if (in_array($effectiveRole, ['admin', 'manager', 'workspace_admin'], true)) {
                $msgCount  = $workspace->messages()->count();
                $fileCount = $workspace->files()->count();
            }
        }
    @endphp

    {{-- ── Page header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                 style="background-color:#0058be;">
                {{ strtoupper(substr($workspace->name, 0, 2)) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface">{{ $workspace->name }}</h2>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-0.5 rounded-full border"
                          style="background:{{ $sc['bg'] }};border-color:{{ $sc['border'] }}33;color:{{ $sc['text'] }};">
                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:{{ $sc['dot'] }};"></span>
                        {{ ucfirst($workspace->status) }}
                    </span>
                </div>
                <p class="font-label-md text-label-md text-outline">
                    {{ $workspace->workspace_code }}
                    &middot; {{ ucfirst($workspace->type) }} workspace
                    @if ($workspace->starts_at)
                        &middot; Started {{ $workspace->starts_at->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('workspace.index') }}"
           class="flex-shrink-0 flex items-center gap-1.5 text-secondary font-label-md text-label-md hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
            All Workspaces
        </a>
    </div>

    {{-- ── Status banner ───────────────────────────────────────────────── --}}
    @if ($workspace->status === 'pending')
        <div class="mb-6 rounded-xl px-5 py-4 flex items-start gap-3"
             style="background:#FFFBEB;border:1px solid rgba(245,158,11,0.30);">
            <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:20px;color:#D97706;">schedule</span>
            <div>
                <p class="font-body-sm font-semibold" style="color:#92400E;">Workspace pending activation</p>
                <p class="font-body-sm text-on-surface-variant mt-0.5">The GVOS team will activate this workspace when your engagement begins.</p>
            </div>
        </div>
    @elseif ($workspace->status === 'active')
        <div class="mb-6 rounded-xl px-5 py-4 flex items-start gap-3"
             style="background:#ECFDF5;border:1px solid rgba(16,185,129,0.30);">
            <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:20px;color:#10B981;">check_circle</span>
            <div>
                <p class="font-body-sm font-semibold" style="color:#065F46;">Workspace is active</p>
                @if ($workspace->ends_at)
                    <p class="font-body-sm text-on-surface-variant mt-0.5">
                        Ends {{ $workspace->ends_at->format('d M Y \a\t H:i') }}
                        @php $hoursLeft = max(0, now()->floatDiffInHours($workspace->ends_at, false)); @endphp
                        &mdash; {{ number_format($hoursLeft, 1) }} hours remaining.
                    </p>
                @endif
            </div>
        </div>
    @elseif ($workspace->status === 'completed')
        <div class="mb-6 rounded-xl px-5 py-4 flex items-start gap-3"
             style="background:#F0FDF4;border:1px solid rgba(5,150,105,0.30);">
            <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:20px;color:#059669;">task_alt</span>
            <div>
                <p class="font-body-sm font-semibold" style="color:#064E3B;">Workspace completed</p>
                <p class="font-body-sm text-on-surface-variant mt-0.5">This workspace has concluded. Contact the GVOS team to discuss next steps.</p>
            </div>
        </div>
    @endif

    {{-- ── Billing warning banner ──────────────────────────────────────── --}}
    @php
        $__billingWorkspace = $workspace;
        $__billingForClient = $isClientRole;
    @endphp
    @include('partials.billing-banner')

    {{-- ── New member orientation ──────────────────────────────────────── --}}
    @php
        $__joinedAt  = $activeMember?->joined_at;
        $__isNew     = $__joinedAt && $__joinedAt->gt(now()->subDays(7));
        $__needsOnb  = $user->needsOnboarding();
    @endphp
    @if ($__isNew || $__needsOnb)
        <div class="mb-6 rounded-xl border px-5 py-4" style="border-color:rgba(0,88,190,0.20);background:rgba(0,88,190,0.04);">
            <div class="flex items-start gap-3 mb-3">
                <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size:22px;">waving_hand</span>
                <div>
                    <p class="font-body-md text-body-md text-on-surface font-semibold">Welcome to {{ $workspace->name }}</p>
                    <p class="font-body-sm text-on-surface-variant mt-0.5">
                        @if ($__needsOnb)
                            You have joined this workspace. Complete your profile setup to get the most out of GVOS.
                        @else
                            You joined this workspace recently. Here is a quick guide to get started.
                        @endif
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm">
                @if ($user->hasRole('talent'))
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">task_alt</span>
                        Check your assigned tasks
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">schedule</span>
                        Log time for each session
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">chat</span>
                        Use chat for updates
                    </div>
                @elseif ($user->hasRole('line_manager'))
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">group</span>
                        Review your team below
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">task_alt</span>
                        Create and assign tasks
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">schedule</span>
                        Approve time logs weekly
                    </div>
                @elseif ($user->hasAnyRole(['individual_client','business_client_admin']))
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">task_alt</span>
                        Track deliverables via tasks
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">receipt</span>
                        Review billing and invoices
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">chat</span>
                        Communicate via chat
                    </div>
                @else
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">visibility</span>
                        Browse workspace activity
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">chat</span>
                        Use chat for questions
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">contact_support</span>
                        Contact your manager for help
                    </div>
                @endif
            </div>
            @if ($__needsOnb)
                <div class="mt-3 pt-3 border-t" style="border-color:rgba(0,88,190,0.20);">
                    <a href="{{ route('onboarding.index') }}"
                       class="inline-flex items-center gap-1.5 text-sm font-semibold text-secondary hover:underline">
                        <span class="material-symbols-outlined" style="font-size:15px;">arrow_forward</span>
                        Complete your profile setup
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Metric strip ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-0 bg-white rounded-xl border border-border-subtle shadow-sm divide-x divide-border-subtle overflow-hidden mb-6">
        <div class="px-5 py-4 flex flex-col gap-1">
            <span class="font-label-md text-[11px] text-outline">Tasks</span>
            <p class="font-headline-md text-headline-md text-on-surface">{{ $totalCount }}</p>
            <p class="font-label-md text-[11px] text-secondary font-semibold">{{ $openCount }} open</p>
        </div>
        <div class="px-5 py-4 flex flex-col gap-1">
            <span class="font-label-md text-[11px] text-outline">Team</span>
            <p class="font-headline-md text-headline-md text-on-surface">{{ $teamActiveCount }}</p>
            <p class="font-label-md text-[11px] text-outline">active members</p>
        </div>
        <div class="px-5 py-4 flex flex-col gap-1">
            <span class="font-label-md text-[11px] text-outline">Time Logs</span>
            <p class="font-headline-md text-headline-md text-on-surface">{{ $timeLogCount }}</p>
            <p class="font-label-md text-[11px] text-outline">{{ $isClientRole ? 'approved' : 'total' }}</p>
        </div>
        <div class="px-5 py-4 flex flex-col gap-1">
            <span class="font-label-md text-[11px] text-outline">Reports</span>
            <p class="font-headline-md text-headline-md text-on-surface">{{ $reportCount }}</p>
            @if ($reportsDraftCount > 0)
                <p class="font-label-md text-[11px] font-semibold" style="color:#D97706;">{{ $reportsDraftCount }} draft</p>
            @else
                <p class="font-label-md text-[11px] text-outline">{{ $isClientRole ? 'published' : 'total' }}</p>
            @endif
        </div>
    </div>

    {{-- ── Main content grid ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-5">

        {{-- Team card (5 cols) --}}
        <div class="lg:col-span-5 bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-border-subtle"
                 style="background:rgba(247,249,251,1);">
                <h3 class="font-label-md text-label-md font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size:16px;">group</span>
                    Your Team
                </h3>
                @if ($canViewMembers)
                    <a href="{{ route('workspace.members.index', $workspace) }}"
                       class="font-label-md text-[11px] font-semibold text-secondary hover:brightness-110 flex items-center gap-0.5">
                        <span class="material-symbols-outlined" style="font-size:13px;">manage_accounts</span>
                        {{ $canManageMembers ? 'Manage' : 'View' }}
                    </a>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-0 divide-x divide-border-subtle border-b border-border-subtle">
                <div class="px-4 py-3 text-center">
                    <p class="font-headline-sm text-headline-sm text-on-surface">{{ $teamManagerCount }}</p>
                    <p class="font-label-md text-[11px] text-outline">Managers</p>
                </div>
                <div class="px-4 py-3 text-center">
                    <p class="font-headline-sm text-headline-sm text-on-surface">{{ $teamTalentCount }}</p>
                    <p class="font-label-md text-[11px] text-outline">Talent</p>
                </div>
            </div>
            <div class="divide-y divide-border-subtle">
                @if ($workspace->primaryManager)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                             style="background:rgba(0,88,190,0.80);">
                            {{ strtoupper(substr($workspace->primaryManager->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-label-md text-label-md text-on-surface font-semibold truncate">{{ $workspace->primaryManager->name }}</p>
                            <p class="font-label-md text-[10px] text-outline">Primary Manager</p>
                        </div>
                    </div>
                @endif
                @if ($workspace->primaryTalent)
                    <div class="flex items-center gap-3 px-5 py-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                             style="background:#10B981;">
                            {{ strtoupper(substr($workspace->primaryTalent->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-label-md text-label-md text-on-surface font-semibold truncate">{{ $workspace->primaryTalent->name }}</p>
                            <p class="font-label-md text-[10px] text-outline">Primary Talent</p>
                        </div>
                    </div>
                @endif
                @if (! $workspace->primaryManager && ! $workspace->primaryTalent)
                    <div class="px-5 py-4">
                        <p class="font-label-md text-label-md text-outline italic">Team to be assigned</p>
                    </div>
                @endif
                @if ($workspace->activeMembers->isNotEmpty())
                    @foreach ($workspace->activeMembers->take(3) as $member)
                        @if ($member->user_id !== $workspace->primary_manager_id && $member->user_id !== $workspace->primary_talent_id)
                        <div class="flex items-center gap-3 px-5 py-2.5">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-on-surface-variant text-xs font-bold flex-shrink-0"
                                 style="background:rgba(0,88,190,0.06);">
                                {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-label-md text-[11px] text-on-surface truncate">{{ $member->user->name ?? 'Unknown' }}</p>
                            </div>
                            <span class="font-label-md text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                  style="background:rgba(0,88,190,0.06);color:#0058be;">
                                {{ $member->roleLabel() }}
                            </span>
                        </div>
                        @endif
                    @endforeach
                    @if ($workspace->activeMembers->count() > 5)
                        <div class="px-5 py-2.5">
                            <a href="{{ route('workspace.members.index', $workspace) }}"
                               class="font-label-md text-[11px] text-secondary hover:underline">
                                + {{ $workspace->activeMembers->count() - 5 }} more members
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Schedule + Kanban summary (7 cols) --}}
        <div class="lg:col-span-7 space-y-5">

            {{-- Schedule card --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-5 pt-4 pb-3 border-b border-border-subtle" style="background:rgba(247,249,251,1);">
                    <h3 class="font-label-md text-label-md font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">calendar_today</span>
                        Schedule & Details
                    </h3>
                </div>
                <div class="grid grid-cols-2 gap-0 divide-x divide-border-subtle">
                    <div class="px-5 py-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-on-surface-variant">Status</span>
                            <span class="font-semibold text-on-surface capitalize">{{ $workspace->status }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-on-surface-variant">Type</span>
                            <span class="font-semibold text-on-surface capitalize">{{ $workspace->type }}</span>
                        </div>
                        @if ($workspace->task_limit > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-on-surface-variant">Task limit</span>
                                <span class="font-semibold text-on-surface">{{ $workspace->task_limit }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="px-5 py-4 space-y-3">
                        @if ($workspace->starts_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-on-surface-variant">Started</span>
                                <span class="font-semibold text-on-surface">{{ $workspace->starts_at->format('d M Y') }}</span>
                            </div>
                        @endif
                        @if ($workspace->ends_at)
                            <div class="flex justify-between text-sm">
                                <span class="text-on-surface-variant">Ends</span>
                                <span class="font-semibold text-on-surface">{{ $workspace->ends_at->format('d M Y') }}</span>
                            </div>
                        @endif
                        @if ($workspace->ends_at && $workspace->status === 'active')
                            @php $hoursLeft = max(0, now()->floatDiffInHours($workspace->ends_at, false)); @endphp
                            <div class="flex justify-between text-sm">
                                <span class="text-on-surface-variant">Remaining</span>
                                <span class="font-semibold" style="color:{{ $hoursLeft < 24 ? '#EF4444' : '#10B981' }};">
                                    {{ number_format($hoursLeft, 1) }}h
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kanban summary --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-5 pt-4 pb-3 flex items-center justify-between border-b border-border-subtle"
                     style="background:rgba(247,249,251,1);">
                    <h3 class="font-label-md text-label-md font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary" style="font-size:16px;">view_kanban</span>
                        Kanban Board
                    </h3>
                    <div class="flex items-center gap-2">
                        @if ($canCreateTask)
                            <a href="{{ route('workspace.tasks.create', $workspace) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border transition-all"
                               style="border-color:#0058be;color:#0058be;">
                                <span class="material-symbols-outlined" style="font-size:13px;">add</span>
                                New Task
                            </a>
                        @endif
                        <a href="{{ route('workspace.tasks.index', $workspace) }}"
                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-white"
                           style="background-color:#0058be;">
                            <span class="material-symbols-outlined" style="font-size:13px;">view_kanban</span>
                            Open Board
                        </a>
                    </div>
                </div>

                @if ($totalCount === 0)
                    <div class="px-5 py-6 text-center">
                        <p class="font-label-md text-label-md text-outline italic">No tasks yet in this workspace.</p>
                        @if ($canCreateTask)
                            <a href="{{ route('workspace.tasks.create', $workspace) }}"
                               class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 rounded-lg text-xs font-semibold text-white"
                               style="background-color:#0058be;">
                                <span class="material-symbols-outlined" style="font-size:14px;">add</span>
                                Create First Task
                            </a>
                        @endif
                    </div>
                @else
                    @php
                        $blockedCount   = $taskCounts['blocked']   ?? 0;
                        $submittedCount = $taskCounts['submitted']  ?? 0;
                        $chipDefs = [
                            'pending'            => ['label' => 'Pending',       'bg' => '#FFFBEB', 'color' => '#D97706'],
                            'in_progress'        => ['label' => 'In Progress',   'bg' => '#EFF6FF', 'color' => '#0058be'],
                            'blocked'            => ['label' => 'Blocked',       'bg' => '#FFF5F5', 'color' => '#DC2626'],
                            'submitted'          => ['label' => 'Submitted',     'bg' => '#F5F3FF', 'color' => '#7C3AED'],
                            'revision_requested' => ['label' => 'Revision Req.', 'bg' => '#FFF7ED', 'color' => '#EA580C'],
                            'approved'           => ['label' => 'Approved',      'bg' => '#F0FDF4', 'color' => '#059669'],
                            'closed'             => ['label' => 'Closed',        'bg' => '#F9FAFB', 'color' => '#6B7280'],
                        ];
                    @endphp
                    <div class="grid grid-cols-3 gap-0 divide-x divide-border-subtle border-b border-border-subtle">
                        <div class="px-4 py-3 text-center">
                            <p class="font-headline-sm text-headline-sm text-on-surface">{{ $totalCount }}</p>
                            <p class="font-label-md text-[11px] text-outline">Total</p>
                        </div>
                        <div class="px-4 py-3 text-center">
                            <p class="font-headline-sm text-headline-sm" style="color:#DC2626;">{{ $blockedCount }}</p>
                            <p class="font-label-md text-[11px] text-outline">Blocked</p>
                        </div>
                        <div class="px-4 py-3 text-center">
                            <p class="font-headline-sm text-headline-sm" style="color:#7C3AED;">{{ $submittedCount }}</p>
                            <p class="font-label-md text-[11px] text-outline">For Review</p>
                        </div>
                    </div>
                    <div class="px-5 py-3 flex flex-wrap gap-1.5">
                        @foreach ($chipDefs as $s => $chip)
                            @if (($taskCounts[$s] ?? 0) > 0)
                                <a href="{{ route('workspace.tasks.index', $workspace) }}"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold hover:opacity-80 transition-opacity"
                                   style="background:{{ $chip['bg'] }};color:{{ $chip['color'] }};border:1px solid {{ $chip['color'] }}33;">
                                    {{ $taskCounts[$s] }} {{ $chip['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                    @php
                        $previewTasks = $workspace->tasks()
                            ->with(['assignedTo'])
                            ->whereIn('status', ['pending','in_progress','blocked','submitted','revision_requested'])
                            ->orderBy('created_at', 'desc')
                            ->limit(3)
                            ->get();
                    @endphp
                    @if ($previewTasks->isNotEmpty())
                        <div class="divide-y divide-border-subtle">
                            @foreach ($previewTasks as $pt)
                                <a href="{{ route('workspace.tasks.show', [$workspace, $pt]) }}"
                                   class="flex items-center justify-between px-5 py-2.5 hover:bg-surface-container-low transition-colors group">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="font-mono text-[10px] text-outline flex-shrink-0">{{ $pt->task_code }}</span>
                                        <span class="font-label-md text-label-md text-on-surface truncate group-hover:text-secondary transition-colors">{{ $pt->title }}</span>
                                    </div>
                                    @if ($pt->due_date)
                                        <span class="font-label-md text-[11px] font-medium flex-shrink-0 ml-2"
                                              style="color:{{ $pt->isOverdue() ? '#DC2626' : '#9CA3AF' }};">
                                            {{ $pt->due_date->format('d M') }}
                                        </span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                        @if ($openCount > 3)
                            <div class="px-5 py-2.5 border-t border-border-subtle">
                                <a href="{{ route('workspace.tasks.index', $workspace) }}"
                                   class="font-label-md text-[11px] font-semibold text-secondary hover:underline">
                                    View all {{ $openCount }} open tasks →
                                </a>
                            </div>
                        @endif
                    @endif
                @endif
            </div>

        </div>
    </div>

    {{-- ── Module cards grid ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">

        {{-- Chat --}}
        <a href="{{ route('workspace.chat.index', $workspace) }}"
           class="bg-white rounded-xl border border-border-subtle shadow-sm p-5 hover:border-secondary/30 hover:shadow-md transition-all group flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">forum</span>
                </div>
                <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                      style="background:rgba(0,88,190,0.06);color:#0058be;">
                    {{ $msgCount }} {{ Str::plural('msg', $msgCount) }}
                </span>
            </div>
            <div>
                <p class="font-label-md text-label-md text-on-surface font-semibold">Workspace Chat</p>
                <p class="font-label-md text-[11px] text-outline mt-0.5">Messages & team updates</p>
            </div>
            <p class="font-label-md text-[11px] font-semibold text-secondary group-hover:underline mt-auto">Open Chat →</p>
        </a>

        {{-- Files --}}
        <a href="{{ route('workspace.files.index', $workspace) }}"
           class="bg-white rounded-xl border border-border-subtle shadow-sm p-5 hover:border-secondary/30 hover:shadow-md transition-all group flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">folder_open</span>
                </div>
                <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                      style="background:rgba(0,88,190,0.06);color:#0058be;">
                    {{ $fileCount }} {{ Str::plural('file', $fileCount) }}
                </span>
            </div>
            <div>
                <p class="font-label-md text-label-md text-on-surface font-semibold">File Library</p>
                <p class="font-label-md text-[11px] text-outline mt-0.5">Briefs, deliverables & attachments</p>
            </div>
            <p class="font-label-md text-[11px] font-semibold text-secondary group-hover:underline mt-auto">Open Files →</p>
        </a>

        {{-- Time Logs --}}
        <a href="{{ route('workspace.time-logs.index', $workspace) }}"
           class="bg-white rounded-xl border border-border-subtle shadow-sm p-5 hover:border-secondary/30 hover:shadow-md transition-all group flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">schedule</span>
                </div>
                <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                      style="background:rgba(0,88,190,0.06);color:#0058be;">
                    {{ $timeLogCount }} {{ Str::plural('log', $timeLogCount) }}
                </span>
            </div>
            <div>
                <p class="font-label-md text-label-md text-on-surface font-semibold">Time Logs</p>
                <p class="font-label-md text-[11px] text-outline mt-0.5">Sessions, durations & daily activity</p>
            </div>
            <p class="font-label-md text-[11px] font-semibold text-secondary group-hover:underline mt-auto">View Logs →</p>
        </a>

        {{-- Reports --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-5 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">summarize</span>
                </div>
                <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                      style="background:rgba(0,88,190,0.06);color:#0058be;">
                    {{ $reportCount }} {{ Str::plural('report', $reportCount) }}
                </span>
            </div>
            <div>
                <p class="font-label-md text-label-md text-on-surface font-semibold">Weekly Reports</p>
                @if ($latestReport)
                    @php
                        $lrColors = ['draft'=>'#94A3B8','submitted'=>'#0058be','approved'=>'#7C3AED','published'=>'#059669'];
                        $lrColor  = $lrColors[$latestReport->status] ?? '#94A3B8';
                    @endphp
                    <p class="font-label-md text-[11px] mt-0.5" style="color:{{ $lrColor }};">
                        Latest: {{ $latestReport->weekLabel() }} &middot; {{ $latestReport->statusLabel() }}
                    </p>
                @elseif ($isManagerRole)
                    <p class="font-label-md text-[11px] text-outline mt-0.5">Generate from workspace activity</p>
                @else
                    <p class="font-label-md text-[11px] text-outline mt-0.5">Published summaries appear here</p>
                @endif
            </div>
            @if ($reportsDraftCount > 0)
                <p class="font-label-md text-[11px] font-semibold" style="color:#D97706;">{{ $reportsDraftCount }} awaiting review</p>
            @endif
            <div class="flex flex-wrap items-center gap-2 mt-auto">
                @if ($isManagerRole)
                    <a href="{{ route('workspace.reports.generate', $workspace) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-white"
                       style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size:12px;">auto_awesome</span>
                        Generate
                    </a>
                @endif
                @if ($latestReport && $isClientRole)
                    <a href="{{ route('workspace.reports.show', [$workspace, $latestReport]) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border"
                       style="border-color:#059669;color:#059669;">
                        View Latest
                    </a>
                @endif
                <a href="{{ route('workspace.reports.index', $workspace) }}"
                   class="font-label-md text-[11px] font-semibold text-secondary hover:underline">
                    All Reports →
                </a>
            </div>
        </div>
    </div>

    {{-- ── Billing + Vault row ──────────────────────────────────────────── --}}
    @if ($canSeeBilling || $canSeeVault)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        @if ($canSeeBilling)
            @php
                $subStatusColor = match($subscription?->status) {
                    'active'      => '#10B981',
                    'trial'       => '#8B5CF6',
                    'payment_due' => '#F59E0B',
                    'overdue'     => '#EF4444',
                    'suspended'   => '#64748B',
                    default       => '#94A3B8',
                };
            @endphp
            <a href="{{ route('workspace.billing.index', $workspace) }}"
               class="bg-white rounded-xl border border-border-subtle shadow-sm p-5 hover:border-secondary/30 hover:shadow-md transition-all group flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                             style="background:rgba(0,88,190,0.06);">
                            <span class="material-symbols-outlined text-secondary" style="font-size:18px;">receipt_long</span>
                        </div>
                        <p class="font-label-md text-label-md text-on-surface font-semibold">Billing</p>
                    </div>
                    @if ($subscription)
                        <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                              style="background:{{ $subStatusColor }}18;color:{{ $subStatusColor }};">
                            {{ $subscription->statusLabel() }}
                        </span>
                    @endif
                </div>
                @if ($subscription)
                    <p class="font-label-md text-[11px] text-outline">
                        {{ $subscription->formattedAmount() }} / {{ $subscription->cycleLabel() }}
                        @if ($subscription->next_billing_date)
                            &middot; Next: {{ $subscription->next_billing_date->format('d M Y') }}
                        @endif
                    </p>
                    @if ($outstandingBalance > 0)
                        <p class="font-label-md text-[11px] font-semibold" style="color:#EF4444;">
                            {{ $subscription->currency }} {{ number_format((float) $outstandingBalance, 2) }} outstanding
                        </p>
                    @else
                        <p class="font-label-md text-[11px] font-medium" style="color:#10B981;">No outstanding balance</p>
                    @endif
                @else
                    <p class="font-label-md text-[11px] text-outline">
                        {{ $invoiceCount > 0 ? $invoiceCount . ' invoice(s)' : 'No subscription configured yet' }}
                    </p>
                @endif
                <p class="font-label-md text-[11px] font-semibold text-secondary group-hover:underline mt-auto">View Billing →</p>
            </a>
        @else
            <div class="bg-white rounded-xl border border-dashed border-border-subtle p-5 opacity-40 cursor-not-allowed flex items-center gap-3">
                <div class="w-9 h-9 bg-surface-container-low rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-outline" style="font-size:18px;">receipt</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md text-on-surface-variant">Billing</p>
                    <p class="font-label-md text-[11px] text-outline mt-0.5">Not available for this role</p>
                </div>
            </div>
        @endif

        @if ($canSeeVault)
            <a href="{{ route('workspace.vault.index', $workspace) }}"
               class="bg-white rounded-xl border border-border-subtle shadow-sm p-5 hover:border-secondary/30 hover:shadow-md transition-all group flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform"
                             style="background:rgba(0,88,190,0.06);">
                            <span class="material-symbols-outlined text-secondary" style="font-size:18px;">lock</span>
                        </div>
                        <p class="font-label-md text-label-md text-on-surface font-semibold">Password Vault</p>
                    </div>
                    <span class="font-label-md text-[10px] font-semibold px-2 py-0.5 rounded-full"
                          style="background:rgba(0,88,190,0.06);color:#0058be;">
                        {{ $vaultItemCount }} {{ Str::plural('item', $vaultItemCount) }}
                    </span>
                </div>
                <p class="font-label-md text-[11px] text-outline">
                    Approved workspace credentials with logged reveal activity.
                </p>
                <p class="font-label-md text-[11px] font-semibold text-secondary group-hover:underline mt-auto">Open Vault →</p>
            </a>
        @endif
    </div>
    @endif

    {{-- ── Back link ────────────────────────────────────────────────────── --}}
    <div class="mt-6 text-center">
        <a href="{{ route('workspace.index') }}"
           class="inline-flex items-center gap-1.5 text-secondary font-label-md text-label-md hover:brightness-110 transition-all">
            <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
            Back to Workspaces
        </a>
    </div>

</x-layouts.gvos>
