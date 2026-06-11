<x-layouts.gvos :title="$workspace->name . ' — Time Logs'">
{{-- Stitch reference: time_tracking_daily_reports_gvos/code.html — Phase 26 Batch 3 --}}

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-1">
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
                <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
                <span>Time Logs</span>
            </div>
            <h2 class="font-headline-md text-headline-md text-on-surface font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size:22px;">schedule</span>
                Time Logs
            </h2>
            <p class="font-label-md text-label-md text-outline mt-0.5">
                {{ $workspace->workspace_code }}
                @if ($isClient) &middot; Showing approved client summaries @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($canCreate)
                <a href="{{ route('workspace.time-logs.create', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                   style="background-color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span>
                    Log Time
                </a>
            @endif
            <a href="{{ route('workspace.reports.index', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#0058be;color:#0058be;">
                <span class="material-symbols-outlined" style="font-size:14px;">summarize</span>
                Reports
            </a>
            <a href="{{ route('workspace.show', $workspace) }}"
               class="inline-flex items-center gap-1.5 text-secondary font-label-md text-label-md hover:brightness-110 transition-all">
                <span class="material-symbols-outlined" style="font-size:16px;">arrow_back</span>
                Workspace
            </a>
        </div>
    </div>

    {{-- ── Flash messages ───────────────────────────────────────────────── --}}
    @if (session('success'))
        <x-portal.alert type="success" class="mb-5">{{ session('success') }}</x-portal.alert>
    @endif
    @if (session('error'))
        <div class="mb-5 flex items-start gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.25);color:#991B1B;">
            <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:18px;">error</span>
            <div>
                {{ session('error') }}
                @if (session('active_timer_url'))
                    <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Timer panel (talent / non-client only) ──────────────────────── --}}
    @if (! $isClient && $canCreate)
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden mb-5">
            <div class="px-5 pt-4 pb-3 border-b border-border-subtle" style="background:rgba(247,249,251,1);">
                <h3 class="font-label-md text-label-md font-bold text-on-surface flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size:16px;">timer</span>
                    Work Timer
                </h3>
            </div>
            <div class="p-5">
                @if ($activeTimer)
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                        <div>
                            <p class="font-label-md text-[11px] font-bold uppercase tracking-wider mb-1" style="color:#10B981;">
                                Timer running
                            </p>
                            <h4 class="font-body-md text-body-md font-semibold text-on-surface">{{ $activeTimer->workspace?->name ?? $workspace->name }}</h4>
                            <p class="font-label-md text-label-md text-on-surface-variant mt-1">
                                Started {{ $activeTimer->started_at?->format('d M Y H:i') }}
                                @if ($activeTimer->task)
                                    &middot; {{ $activeTimer->task->task_code }} — {{ Str::limit($activeTimer->task->title, 50) }}
                                @endif
                            </p>
                            <p class="font-mono text-2xl font-bold mt-3 js-running-timer"
                               data-started-at="{{ $activeTimer->started_at?->toIso8601String() }}"
                               style="color:#0058be;">
                                {{ $activeTimer->durationForHumans() }}
                            </p>
                        </div>
                        <div class="w-full lg:w-80 space-y-2">
                            <form method="POST" action="{{ route('workspace.time-tracker.stop', $workspace) }}">
                                @csrf
                                <input type="hidden" name="time_log_id" value="{{ $activeTimer->id }}">
                                <input type="hidden" name="status" value="draft">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold border transition-all"
                                        style="border-color:#F59E0B;color:#92400E;background:rgba(245,158,11,0.08);">
                                    <span class="material-symbols-outlined" style="font-size:16px;">stop_circle</span>
                                    Clock Out
                                </button>
                            </form>
                            <form method="POST" action="{{ route('workspace.time-tracker.complete', $workspace) }}" class="space-y-2">
                                @csrf
                                <input type="hidden" name="time_log_id" value="{{ $activeTimer->id }}">
                                <input type="text" name="work_summary" required maxlength="1000"
                                       placeholder="Work summary for review"
                                       class="w-full px-3 py-2 rounded-lg border border-border-subtle text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                                        style="background-color:#0058be;">
                                    <span class="material-symbols-outlined" style="font-size:16px;">task_alt</span>
                                    Complete Work Session
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif ($userActiveTimer)
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-secondary" style="font-size:20px;">timer</span>
                        <p class="font-label-md text-label-md text-on-surface-variant">
                            You have a running timer in another workspace.
                            <a href="{{ route('workspace.time-logs.show', [$userActiveTimer->workspace, $userActiveTimer]) }}"
                               class="font-semibold underline" style="color:#0058be;">View active timer</a>
                        </p>
                    </div>
                @else
                    <form method="POST" action="{{ route('workspace.time-tracker.start', $workspace) }}" class="flex flex-col lg:flex-row gap-3 lg:items-end">
                        @csrf
                        <div class="flex-1">
                            <label class="block font-label-md text-[11px] font-semibold text-on-surface mb-1">Related task (optional)</label>
                            <select name="workspace_task_id"
                                    class="w-full px-3 py-2 rounded-lg border border-border-subtle text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                                <option value="">No specific task</option>
                                @foreach ($startableTasks as $task)
                                    <option value="{{ $task->id }}">{{ $task->task_code }} — {{ Str::limit($task->title, 64) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                                style="background-color:#0058be;">
                            <span class="material-symbols-outlined" style="font-size:16px;">timer</span>
                            Start Timer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Running timers panel (manager view) ────────────────────────── --}}
    @if ($canReview && $runningTimers->isNotEmpty())
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden mb-5">
            <div class="px-5 pt-4 pb-3 border-b border-border-subtle" style="background:rgba(247,249,251,1);">
                <div class="flex items-center justify-between">
                    <h3 class="font-label-md text-label-md font-bold text-on-surface flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:16px;color:#10B981;">radio_button_checked</span>
                        Running Timers
                    </h3>
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[10px] font-bold"
                          style="background:#10B981;">{{ $runningTimers->count() }}</span>
                </div>
            </div>
            <div class="divide-y divide-border-subtle">
                @foreach ($runningTimers as $runningTimer)
                    <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 style="background:#10B981;">
                                {{ strtoupper(substr($runningTimer->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-label-md text-label-md text-on-surface font-semibold">{{ $runningTimer->user->name ?? 'Unknown user' }}</p>
                                <p class="font-label-md text-[11px] text-outline">
                                    Started {{ $runningTimer->started_at?->format('d M Y H:i') }}
                                    @if ($runningTimer->task)
                                        &middot; {{ $runningTimer->task->task_code }} — {{ Str::limit($runningTimer->task->title, 40) }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm font-bold js-running-timer"
                                  data-started-at="{{ $runningTimer->started_at?->toIso8601String() }}"
                                  style="color:#10B981;">{{ $runningTimer->durationForHumans() }}</span>
                            <a href="{{ route('workspace.time-logs.show', [$workspace, $runningTimer]) }}"
                               class="font-label-md text-[11px] font-semibold hover:underline" style="color:#0058be;">View</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Time logs list ───────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">

        <div class="px-5 pt-4 pb-3 border-b border-border-subtle flex items-center justify-between"
             style="background:rgba(247,249,251,1);">
            <h3 class="font-label-md text-label-md font-bold text-on-surface">Log History</h3>
            @if ($canCreate)
                <a href="{{ route('workspace.time-logs.create', $workspace) }}"
                   class="inline-flex items-center gap-1 text-secondary font-label-md text-[11px] font-semibold hover:underline">
                    <span class="material-symbols-outlined" style="font-size:13px;">add</span>
                    Manual entry
                </a>
            @endif
        </div>

        @if ($timeLogs->isEmpty())
            @php $__tlUser = auth()->user(); @endphp
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                     style="background:rgba(0,88,190,0.06);">
                    <span class="material-symbols-outlined text-secondary" style="font-size:26px;">schedule</span>
                </div>
                <h4 class="font-label-md text-label-md font-semibold text-on-surface mb-2">No time logs yet</h4>
                @if ($canCreate)
                    <p class="font-label-md text-[11px] text-outline max-w-xs mx-auto mb-4">
                        Record your work sessions here. Start a timer above or log time manually.
                    </p>
                    <a href="{{ route('workspace.time-logs.create', $workspace) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                       style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size:16px;">add</span>
                        Log Time Manually
                    </a>
                @elseif ($__tlUser->hasAnyRole(['line_manager']))
                    <p class="font-label-md text-[11px] text-outline max-w-xs mx-auto">
                        No time logs submitted yet. They will appear here once talent starts logging sessions.
                    </p>
                @elseif ($__tlUser->hasAnyRole(['individual_client','business_client_admin','business_client_staff']))
                    <p class="font-label-md text-[11px] text-outline max-w-xs mx-auto">
                        Approved time logs appear here once your manager reviews sessions.
                    </p>
                @else
                    <p class="font-label-md text-[11px] text-outline max-w-xs mx-auto">
                        No time logs are available for this workspace yet.
                    </p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-border-subtle" style="background:rgba(247,249,251,1);">
                            <th class="text-left px-5 py-3 font-label-md text-[11px] font-semibold text-outline">Date</th>
                            @if (!$isClient)
                                <th class="text-left px-5 py-3 font-label-md text-[11px] font-semibold text-outline">Logged by</th>
                            @endif
                            <th class="text-left px-5 py-3 font-label-md text-[11px] font-semibold text-outline">Summary</th>
                            <th class="text-left px-5 py-3 font-label-md text-[11px] font-semibold text-outline">Duration</th>
                            <th class="text-left px-5 py-3 font-label-md text-[11px] font-semibold text-outline hidden sm:table-cell">Task</th>
                            @if (!$isClient)
                                <th class="text-left px-5 py-3 font-label-md text-[11px] font-semibold text-outline">Status</th>
                            @endif
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-subtle">
                        @foreach ($timeLogs as $log)
                            @php
                                $statusColors = [
                                    'draft'     => '#94A3B8',
                                    'running'   => '#10B981',
                                    'submitted' => '#0058be',
                                    'reviewed'  => '#7C3AED',
                                    'approved'  => '#059669',
                                    'rejected'  => '#DC2626',
                                ];
                                $sc = $statusColors[$log->status] ?? '#94A3B8';
                            @endphp
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-5 py-3 font-label-md text-label-md text-on-surface whitespace-nowrap">
                                    {{ $log->log_date ? $log->log_date->format('d M Y') : '—' }}
                                </td>
                                @if (!$isClient)
                                    <td class="px-5 py-3 font-label-md text-label-md text-on-surface-variant">
                                        {{ $log->user->name ?? '—' }}
                                    </td>
                                @endif
                                <td class="px-5 py-3 font-label-md text-label-md text-on-surface max-w-xs">
                                    @if ($isClient && $log->client_visible_summary)
                                        {{ Str::limit($log->client_visible_summary, 80) }}
                                    @else
                                        {{ Str::limit($log->work_summary, 80) }}
                                    @endif
                                </td>
                                <td class="px-5 py-3 font-mono text-sm font-semibold text-on-surface whitespace-nowrap">
                                    {{ $log->durationForHumans() }}
                                </td>
                                <td class="px-5 py-3 font-label-md text-label-md text-on-surface-variant hidden sm:table-cell">
                                    {{ $log->task ? Str::limit($log->task->title, 36) : '—' }}
                                </td>
                                @if (!$isClient)
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full font-label-md text-[10px] font-semibold"
                                              style="background:{{ $sc }}18;color:{{ $sc }};">
                                            {{ $log->statusLabel() }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('workspace.time-logs.show', [$workspace, $log]) }}"
                                       class="font-label-md text-[11px] font-semibold hover:underline transition-all"
                                       style="color:#0058be;">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($timeLogs->hasPages())
                <div class="px-5 py-3 border-t border-border-subtle">
                    {{ $timeLogs->links() }}
                </div>
            @endif
        @endif
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function formatElapsed(startedAt) {
            var started = new Date(startedAt);
            var totalSeconds = Math.max(0, Math.floor((Date.now() - started.getTime()) / 1000));
            var hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
            var minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
            var seconds = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
            return hours + ':' + minutes + ':' + seconds;
        }

        document.querySelectorAll('.js-running-timer[data-started-at]').forEach(function (timer) {
            var tick = function () {
                timer.textContent = formatElapsed(timer.dataset.startedAt);
            };
            tick();
            window.setInterval(tick, 1000);
        });
    });
</script>

</x-layouts.gvos>
