<x-layouts.gvos :title="$workspace->name">

    <div class="max-w-4xl mx-auto space-y-8">

        @php
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

        {{-- ── Coming soon placeholder ───────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-border-subtle shadow-card p-6">
            <div class="text-center py-6">
                <div class="w-12 h-12 bg-secondary/5 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 24px;">task_alt</span>
                </div>
                <h4 class="text-sm font-semibold text-on-surface mb-1">Tasks, files, and chat coming soon</h4>
                <p class="text-xs text-on-surface-variant max-w-sm mx-auto">The GVOS team is setting up your full workspace. Task board and file sharing will be available in a future update.</p>
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
