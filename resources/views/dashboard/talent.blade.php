{{-- Phase 28 — Talent home. Answers "what am I doing right now?" before anything else. --}}
<x-layouts.gvos title="Home">

@php
    $first     = auth()->user()->profile?->first_name ?? explode(' ', auth()->user()->name)[0];
    $weekHours = intdiv($weekMinutes, 60);
    $weekMins  = $weekMinutes % 60;
    $openCount = $myTasks->count();
    $timerWs   = $workspaces->first();
@endphp

@php $__obUser = auth()->user(); @endphp
@include('partials.onboarding-banner')

<x-portal.page-header
    title="Hello, {{ $first }}"
    :subtitle="$activeTimer
        ? 'You are clocked in. Stop the timer when you finish this session.'
        : ($openCount > 0
            ? 'You have ' . $openCount . ' open ' . Str::plural('task', $openCount) . '. Start with the one below.'
            : ($workspaces->isEmpty()
                ? 'You have not been added to a workspace yet.'
                : 'Nothing is waiting on you right now.'))"
    :divider="false" />

@if (session('success'))
    <x-portal.alert type="success">
        {{ session('success') }}
        @if (session('active_timer_url'))
            <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
        @endif
    </x-portal.alert>
@elseif (session('error'))
    <x-portal.alert type="error">
        {{ session('error') }}
        @if (session('active_timer_url'))
            <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
        @endif
    </x-portal.alert>
@endif

{{-- ── 1. Now: timer + focus task ──────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

    {{-- Timer — the talent's most-used control, first on every screen size --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-card p-5 flex flex-col">
        @if ($activeTimer)
            <div class="flex items-center gap-2 mb-3">
                <span class="w-2 h-2 rounded-full animate-pulse" style="background:#10B981;"></span>
                <span class="text-[12px] font-semibold" style="color:#047857;">Clocked in</span>
            </div>
            <p class="font-mono-sm text-[30px] font-bold text-on-surface leading-none js-running-timer tracking-tight"
               data-started-at="{{ $activeTimer->started_at?->toIso8601String() }}">
                {{ $activeTimer->durationForHumans() }}
            </p>
            <p class="text-[12.5px] text-on-surface-variant mt-2">
                {{ $activeTimer->workspace?->name }}
                @if ($activeTimer->task) · {{ $activeTimer->task->title }} @endif
            </p>

            <form method="POST" action="{{ route('workspace.time-tracker.complete', $activeTimer->workspace) }}" class="mt-4 space-y-2">
                @csrf
                <input type="hidden" name="time_log_id" value="{{ $activeTimer->id }}">
                <label for="talent-summary" class="sr-only">What did you work on?</label>
                <input id="talent-summary" type="text" name="work_summary" required maxlength="1000"
                       placeholder="What did you work on?"
                       class="w-full px-3 py-2 rounded-lg border border-border-subtle text-[13.5px] focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                <x-portal.btn variant="primary" icon="task_alt" class="w-full">Finish &amp; Submit Session</x-portal.btn>
            </form>
            <form method="POST" action="{{ route('workspace.time-tracker.stop', $activeTimer->workspace) }}" class="mt-2">
                @csrf
                <input type="hidden" name="time_log_id" value="{{ $activeTimer->id }}">
                <input type="hidden" name="status" value="draft">
                <x-portal.btn variant="ghost" size="sm" icon="pause_circle" class="w-full">Pause &amp; save as draft</x-portal.btn>
            </form>
        @elseif ($timerWs)
            <p class="text-[12px] text-outline mb-1">Time tracking</p>
            <p class="text-[15px] font-semibold text-on-surface">Not clocked in</p>
            <p class="text-[12.5px] text-on-surface-variant mt-1 mb-4">
                {{ $weekHours }}h {{ $weekMins }}m approved this week.
            </p>
            <form id="talent-start-timer" method="POST"
                  action="{{ route('workspace.time-tracker.start', $timerWs) }}" class="mt-auto space-y-2">
                @csrf
                @if ($workspaces->count() > 1)
                    <label for="timer-ws" class="sr-only">Workspace</label>
                    <select id="timer-ws" class="w-full px-3 py-2 rounded-lg border border-border-subtle text-[13.5px]">
                        @foreach ($workspaces as $w)
                            <option value="{{ $w->id }}" data-start-url="{{ route('workspace.time-tracker.start', $w) }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                @endif
                <label for="timer-task" class="sr-only">Task</label>
                <select id="timer-task" name="workspace_task_id"
                        class="w-full px-3 py-2 rounded-lg border border-border-subtle text-[13.5px]">
                    <option value="">No specific task</option>
                    @foreach ($timerTasks as $t)
                        <option value="{{ $t->id }}" data-workspace-id="{{ $t->workspace_id }}">{{ $t->task_code }} — {{ $t->title }}</option>
                    @endforeach
                </select>
                <x-portal.btn variant="primary" icon="play_circle" class="w-full">Clock In</x-portal.btn>
            </form>
        @else
            <p class="text-[12px] text-outline mb-1">Time tracking</p>
            <p class="text-[15px] font-semibold text-on-surface">Unavailable</p>
            <p class="text-[12.5px] text-on-surface-variant mt-1">
                Your timer appears once you are added to a workspace.
            </p>
        @endif
    </div>

    {{-- Focus task --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-border-subtle shadow-card p-5 flex flex-col">
        @if ($focusTask)
            <p class="text-[12px] text-outline mb-2">Start here</p>
            {{-- Not a link: the "Open Task" button below is the single action
                 for this card, so the title must not duplicate its href. --}}
            <p class="text-[18px] font-semibold text-on-surface leading-snug">{{ $focusTask->title }}</p>
            <div class="flex items-center gap-2 flex-wrap mt-2.5">
                <x-portal.status-badge :status="$focusTask->status" />
                @if ($focusTask->isOverdue())
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                          style="color:#B91C1C;background:rgba(239,68,68,0.10);">
                        Overdue · due {{ $focusTask->due_date->format('j M') }}
                    </span>
                @elseif ($focusTask->due_date)
                    <span class="text-[11.5px] text-on-surface-variant">Due {{ $focusTask->due_date->format('j M') }}</span>
                @endif
                <span class="text-[11.5px] text-outline">{{ $focusTask->workspace?->name }}</span>
            </div>
            @if ($focusTask->description)
                <p class="text-[13.5px] text-on-surface-variant mt-3 leading-relaxed line-clamp-3">
                    {{ Str::limit(strip_tags($focusTask->description), 220) }}
                </p>
            @endif
            {{-- Secondary, not primary: "Clock In" is the one primary action
                 above the fold on this page. --}}
            <div class="mt-auto pt-4 flex items-center gap-2">
                <x-portal.btn variant="secondary" icon="open_in_new"
                              :href="route('workspace.tasks.show', [$focusTask->workspace_id, $focusTask])">
                    Open Task
                </x-portal.btn>
                <x-portal.btn variant="ghost"
                              :href="route('workspace.tasks.index', $focusTask->workspace_id)">
                    View Board
                </x-portal.btn>
            </div>
        @else
            <x-portal.empty-state
                icon="task_alt"
                title="{{ $workspaces->isEmpty() ? 'No workspace yet' : 'No open tasks' }}"
                message="{{ $workspaces->isEmpty()
                    ? 'Your GVOS manager will add you to a workspace. It will appear here as soon as it is ready.'
                    : 'Everything assigned to you is done. New work will appear here when your manager assigns it.' }}" />
        @endif
    </div>
</div>

{{-- ── 2. My work ───────────────────────────────────────────────────────────── --}}
@if ($myTasks->count() > 1 || $logsNeedingAction->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

        <div class="lg:col-span-2">
            <x-portal.section title="My work" subtitle="Most urgent first" flush>
                <x-slot:actions>
                    @if ($timerWs)
                        <a href="{{ route('workspace.tasks.index', $timerWs) }}"
                           class="text-[12.5px] font-semibold text-secondary hover:underline">Open board</a>
                    @endif
                </x-slot:actions>
                <div class="divide-y divide-border-subtle">
                    @foreach ($myTasks->skip(1)->take(6) as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$t->workspace_id, $t])"
                            :title="$t->title"
                            :meta="$t->workspace?->name . ($t->due_date ? ' · due ' . $t->due_date->format('j M') : '')"
                            :badge="$t->statusLabel()"
                            :tone="$t->status === 'blocked' ? 'urgent' : ($t->isOverdue() ? 'warn' : ($t->status === 'revision_requested' ? 'warn' : 'info'))"
                            action="Open" />
                    @endforeach
                </div>
            </x-portal.section>
        </div>

        <div class="space-y-5">
            @if ($logsNeedingAction->isNotEmpty())
                <x-portal.section title="Your time entries" flush>
                    <div class="divide-y divide-border-subtle">
                        @foreach ($logsNeedingAction as $log)
                            <x-portal.attention-item
                                :href="route('workspace.time-logs.show', [$log->workspace_id, $log])"
                                :title="$log->status === 'rejected' ? 'Time entry needs changes' : 'Draft time entry'"
                                :meta="$log->workspace?->name . ' · ' . $log->log_date?->format('j M') . ' · ' . $log->durationForHumans()"
                                :tone="$log->status === 'rejected' ? 'warn' : 'default'"
                                :action="$log->status === 'rejected' ? 'Fix' : 'Submit'" />
                        @endforeach
                    </div>
                </x-portal.section>
            @endif

            @if ($latestMessage)
                <x-portal.section title="Latest message">
                    <p class="text-[12px] text-outline mb-1.5">
                        {{ $latestMessage->user?->name }} · {{ $latestMessage->workspace?->name }}
                    </p>
                    <p class="text-[13.5px] text-on-surface-variant leading-relaxed">
                        {{ Str::limit($latestMessage->message, 180) }}
                    </p>
                    <a href="{{ route('workspace.chat.index', $latestMessage->workspace_id) }}"
                       class="inline-flex items-center gap-1 mt-3 text-[12.5px] font-semibold text-secondary hover:underline">
                        Open messages
                        <span class="material-symbols-outlined" style="font-size:15px;">chevron_right</span>
                    </a>
                </x-portal.section>
            @endif
        </div>
    </div>
@endif

{{-- ── 3. Workspaces + supporting numbers ───────────────────────────────────── --}}
@if ($workspaces->isNotEmpty())
    <x-portal.section title="Your workspaces" flush class="mb-6">
        <div class="divide-y divide-border-subtle">
            @foreach ($workspaces as $w)
                <x-portal.workspace-row
                    :workspace="$w"
                    :meta="$w->primaryManager ? 'Manager · ' . $w->primaryManager->name : null" />
            @endforeach
        </div>
    </x-portal.section>

    <x-portal.metric-strip :metrics="[
        ['label' => 'Open tasks', 'value' => $openCount],
        $blockedCount > 0 ? ['label' => 'Blocked', 'value' => $blockedCount, 'tone' => 'urgent'] : null,
        $overdueCount > 0 ? ['label' => 'Overdue', 'value' => $overdueCount, 'tone' => 'warn'] : null,
        ['label' => 'Approved this week', 'value' => $weekHours . 'h ' . $weekMins . 'm', 'tone' => 'muted'],
    ]" />
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var wsSel = document.getElementById('timer-ws');
        var taskSel = document.getElementById('timer-task');
        var form = document.getElementById('talent-start-timer');
        if (!taskSel || !form) return;

        function sync() {
            var wsId = wsSel ? wsSel.value : null;
            if (wsSel) {
                form.action = wsSel.options[wsSel.selectedIndex].dataset.startUrl;
            }
            taskSel.querySelectorAll('option[data-workspace-id]').forEach(function (o) {
                var visible = !wsId || o.dataset.workspaceId === wsId;
                o.hidden = !visible;
                if (!visible && o.selected) taskSel.value = '';
            });
        }
        if (wsSel) wsSel.addEventListener('change', sync);
        sync();
    });
</script>

</x-layouts.gvos>
