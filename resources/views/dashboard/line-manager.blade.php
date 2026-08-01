{{-- Phase 28 — Manager command center. Opens with the actual review queue,
     each row deep-linked to the specific item. --}}
<x-layouts.gvos title="Command Center">

@php
    $logs    = $queue['logs'];
    $tasks   = $queue['tasks'];
    $reports = $queue['reports'];
@endphp

@php $__obUser = auth()->user(); @endphp
@include('partials.onboarding-banner')

<x-portal.page-header
    title="Command Center"
    :subtitle="$queueTotal > 0
        ? $queueTotal . ' ' . Str::plural('item', $queueTotal) . ' waiting on you across ' . $workspaces->count() . ' ' . Str::plural('workspace', $workspaces->count()) . '.'
        : ($workspaces->isEmpty()
            ? 'You are not supervising any workspaces yet.'
            : 'Nothing is waiting on you. ' . $workspaces->count() . ' ' . Str::plural('workspace', $workspaces->count()) . ' running normally.')"
    :divider="false" />

@if (session('success'))
    <x-portal.alert type="success">{{ session('success') }}</x-portal.alert>
@elseif (session('error'))
    <x-portal.alert type="error">{{ session('error') }}</x-portal.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

    {{-- ── Review queue — the reason this page exists ────────────────────── --}}
    <div class="lg:col-span-2">
        <x-portal.section id="review-queue" title="Needs your review" flush>
            <x-slot:actions>
                @if ($queueTotal > 0)
                    <span class="text-[12px] font-semibold px-2 py-0.5 rounded-full"
                          style="color:#92400E;background:rgba(245,158,11,0.12);">{{ $queueTotal }}</span>
                @endif
            </x-slot:actions>

            @if ($queueTotal === 0)
                <x-portal.empty-state
                    compact
                    icon="check_circle"
                    title="Your queue is clear"
                    message="Submitted time entries, work awaiting approval and report drafts appear here." />
            @else
                <div class="divide-y divide-border-subtle">

                    @foreach ($logs as $log)
                        <x-portal.attention-item
                            :href="route('workspace.time-logs.show', [$log->workspace_id, $log])"
                            :title="'Time entry · ' . $log->durationForHumans() . ' · ' . ($log->user?->name ?? 'Talent')"
                            :meta="$log->workspace?->name . ' · ' . $log->log_date?->format('j M') . ' · ' . Str::limit($log->work_summary, 70)"
                            icon="schedule"
                            tone="info"
                            badge="Time entry"
                            action="Review" />
                    @endforeach

                    @foreach ($tasks as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$t->workspace_id, $t])"
                            :title="$t->title"
                            :meta="$t->workspace?->name . ' · submitted by ' . ($t->assignedTo?->name ?? 'talent') . ($t->submitted_at ? ' · ' . $t->submitted_at->diffForHumans() : '')"
                            icon="task_alt"
                            tone="warn"
                            badge="Awaiting review"
                            action="Review" />
                    @endforeach

                    @foreach ($reports as $r)
                        <x-portal.attention-item
                            :href="route('workspace.reports.show', [$r->workspace_id, $r])"
                            :title="'Weekly report · ' . $r->weekLabel()"
                            :meta="$r->workspace?->name . ' · ' . $r->statusLabel() . ' · not yet published to the client'"
                            icon="summarize"
                            tone="default"
                            badge="Report"
                            action="Publish" />
                    @endforeach

                </div>
            @endif
        </x-portal.section>

        {{-- Blocked work is separate — it needs unblocking, not approving. --}}
        @if ($blockedTasks->isNotEmpty())
            <x-portal.section title="Blocked work" subtitle="Talent cannot continue until these are resolved" flush class="mt-6">
                <div class="divide-y divide-border-subtle">
                    @foreach ($blockedTasks as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$t->workspace_id, $t])"
                            :title="$t->title"
                            :meta="$t->workspace?->name . ' · ' . ($t->assignedTo?->name ?? 'Unassigned') . ($t->due_date ? ' · due ' . $t->due_date->format('j M') : '')"
                            icon="block"
                            tone="urgent"
                            badge="Blocked"
                            action="Open" />
                    @endforeach
                </div>
            </x-portal.section>
        @endif
    </div>

    {{-- ── Right column: who is working, then workspaces ─────────────────── --}}
    <div class="space-y-5">

        <x-portal.section title="Working now" flush>
            @if ($runningTimers->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="timer_off"
                    title="No one is clocked in"
                    message="Running work sessions appear here." />
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($runningTimers as $rt)
                        <a href="{{ route('workspace.time-logs.show', [$rt->workspace_id, $rt]) }}"
                           class="flex items-center gap-3 px-4 py-3 hover:bg-surface-container-low transition-colors">
                            <span class="w-2 h-2 rounded-full animate-pulse flex-shrink-0" style="background:#10B981;"></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[13.5px] font-medium text-on-surface truncate">{{ $rt->user?->name }}</span>
                                <span class="block text-[12px] text-outline truncate">{{ $rt->workspace?->name }}</span>
                            </span>
                            <span class="font-mono-sm text-[12.5px] font-semibold flex-shrink-0 js-running-timer"
                                  style="color:#047857;"
                                  data-started-at="{{ $rt->started_at?->toIso8601String() }}">{{ $rt->durationForHumans() }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-portal.section>

        <x-portal.section title="Your workspaces" flush>
            @if ($workspaces->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="workspaces"
                    title="No workspaces assigned"
                    message="A GVOS administrator will assign workspaces to you." />
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($workspaces as $w)
                        @php
                            $c = $perWorkspace[$w->id] ?? ['logs' => 0, 'tasks' => 0, 'blocked' => 0, 'reports' => 0];
                            $alerts = [];
                            if ($c['blocked'] > 0) {
                                $alerts[] = ['label' => $c['blocked'] . ' blocked', 'tone' => 'urgent'];
                            }
                            if ($c['logs'] + $c['tasks'] > 0) {
                                $alerts[] = ['label' => ($c['logs'] + $c['tasks']) . ' to review', 'tone' => 'warn'];
                            }
                        @endphp
                        <x-portal.workspace-row
                            :workspace="$w"
                            :meta="$w->primaryTalent?->name"
                            :alerts="$alerts" />
                    @endforeach
                </div>
            @endif
        </x-portal.section>

    </div>
</div>

{{-- ── Supporting numbers, deliberately last ────────────────────────────── --}}
@if ($workspaces->isNotEmpty())
    <x-portal.metric-strip :metrics="[
        ['label' => 'Workspaces', 'value' => $workspaces->count()],
        ['label' => 'Time entries to review', 'value' => $logs->count(), 'tone' => $logs->count() ? 'warn' : 'muted'],
        ['label' => 'Work awaiting approval', 'value' => $tasks->count(), 'tone' => $tasks->count() ? 'warn' : 'muted'],
        ['label' => 'Blocked', 'value' => $blockedTasks->count(), 'tone' => $blockedTasks->count() ? 'urgent' : 'muted'],
        ['label' => 'Reports to publish', 'value' => $reports->count(), 'tone' => 'muted'],
    ]" />
@endif

</x-layouts.gvos>
