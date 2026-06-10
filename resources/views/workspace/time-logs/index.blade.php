<x-layouts.gvos :title="$workspace->name . ' — Time Logs'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Time Logs</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">schedule</span>
                Time Logs
            </h2>
            <p class="text-xs text-outline mt-0.5">{{ $workspace->workspace_code }}
                @if ($isClient) &middot; Showing approved summaries @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if ($canCreate)
                <a href="{{ route('workspace.time-logs.create', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition-all text-white"
                   style="background-color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                    Log Time
                </a>
            @endif
            <a href="{{ route('workspace.reports.index', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#0058be; color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 14px;">summarize</span>
                Reports
            </a>
            <a href="{{ route('workspace.show', $workspace) }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Workspace
            </a>
        </div>
    </div>

    {{-- ── Session flash ─────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#065F46;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Time logs table ──────────────────────────────────────────────── --}}
    @if (session('error'))
        <div class="mb-4 flex items-start gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.25);color:#991B1B;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">error</span>
            <div>
                {{ session('error') }}
                @if (session('active_timer_url'))
                    <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
                @endif
            </div>
        </div>
    @endif

    @if (! $isClient && $canCreate)
        <div class="mb-5 bg-white rounded-xl border border-border-subtle shadow-sm p-5">
            @if ($activeTimer)
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide" style="color:#10B981;">Timer running</p>
                        <h3 class="text-base font-bold text-on-surface mt-1">{{ $activeTimer->workspace?->name ?? $workspace->name }}</h3>
                        <p class="text-sm text-on-surface-variant mt-1">
                            Started {{ $activeTimer->started_at?->format('d M Y H:i') }}
                            @if ($activeTimer->task)
                                &middot; {{ $activeTimer->task->task_code }} - {{ Str::limit($activeTimer->task->title, 50) }}
                            @endif
                        </p>
                        <p class="font-mono text-lg font-bold text-primary mt-2 js-running-timer"
                           data-started-at="{{ $activeTimer->started_at?->toIso8601String() }}">
                            {{ $activeTimer->durationForHumans() }}
                        </p>
                    </div>
                    <div class="w-full lg:w-80 space-y-2">
                        <form method="POST" action="{{ route('workspace.time-tracker.stop', $workspace) }}">
                            @csrf
                            <input type="hidden" name="time_log_id" value="{{ $activeTimer->id }}">
                            <input type="hidden" name="status" value="draft">
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border"
                                    style="border-color:#F59E0B;color:#92400E;background:rgba(245,158,11,0.08);">
                                <span class="material-symbols-outlined" style="font-size: 16px;">stop_circle</span>
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
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                                    style="background-color:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">task_alt</span>
                                Complete Work Session
                            </button>
                        </form>
                    </div>
                </div>
            @elseif ($userActiveTimer)
                <p class="text-sm text-on-surface-variant">
                    You already have a running timer in another workspace.
                    <a href="{{ route('workspace.time-logs.show', [$userActiveTimer->workspace, $userActiveTimer]) }}"
                       class="font-semibold underline" style="color:#0058be;">View active timer</a>
                </p>
            @else
                <form method="POST" action="{{ route('workspace.time-tracker.start', $workspace) }}" class="flex flex-col lg:flex-row gap-3 lg:items-end">
                    @csrf
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-on-surface mb-1">Related task (optional)</label>
                        <select name="workspace_task_id"
                                class="w-full px-3 py-2 rounded-lg border border-border-subtle text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                            <option value="">No specific task</option>
                            @foreach ($startableTasks as $task)
                                <option value="{{ $task->id }}">{{ $task->task_code }} - {{ Str::limit($task->title, 64) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white"
                            style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">timer</span>
                        Start Timer
                    </button>
                </form>
            @endif
        </div>
    @endif

    @if ($canReview && $runningTimers->isNotEmpty())
        <div class="mb-5 bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-border-subtle">
                <h3 class="text-sm font-bold text-on-surface">Running timers</h3>
            </div>
            <div class="divide-y divide-[#F1F5F9]">
                @foreach ($runningTimers as $runningTimer)
                    <div class="px-5 py-3 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-on-surface">{{ $runningTimer->user->name ?? 'Unknown user' }}</p>
                            <p class="text-xs text-on-surface-variant">
                                Started {{ $runningTimer->started_at?->format('d M Y H:i') }}
                                @if ($runningTimer->task)
                                    &middot; {{ $runningTimer->task->task_code }} - {{ Str::limit($runningTimer->task->title, 50) }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-sm font-bold js-running-timer"
                                  data-started-at="{{ $runningTimer->started_at?->toIso8601String() }}">{{ $runningTimer->durationForHumans() }}</span>
                            <a href="{{ route('workspace.time-logs.show', [$workspace, $runningTimer]) }}"
                               class="text-xs font-semibold hover:underline" style="color:#0058be;">View</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">

        @if ($timeLogs->isEmpty())
            @php $__tlUser = auth()->user(); @endphp
            <div class="p-12 text-center">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                     style="background-color:rgba(0,88,190,.06);">
                    <span class="material-symbols-outlined" style="font-size: 26px; color:#0058be;">schedule</span>
                </div>
                <h4 class="text-sm font-semibold mb-2" style="color:#1E293B;">No time logs yet</h4>
                @if ($canCreate)
                    <p class="text-xs max-w-xs mx-auto mb-4" style="color:#94A3B8;">
                        Record your work sessions here. Start by logging your first session.
                    </p>
                    <a href="{{ route('workspace.time-logs.create', $workspace) }}"
                       class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all"
                       style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                        Log Time
                    </a>
                @elseif ($__tlUser->hasAnyRole(['line_manager']))
                    <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                        No time logs have been submitted by your team yet. They will appear here once talent starts logging sessions.
                    </p>
                @elseif ($__tlUser->hasAnyRole(['individual_client','business_client_admin','business_client_staff']))
                    <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                        Approved time logs will appear here once your manager approves sessions. This gives you a clear view of time spent on your project.
                    </p>
                @else
                    <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                        No time logs are available for this workspace yet.
                    </p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background-color:#F8FAFC;border-bottom:1px solid #E2E8F0;">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-outline">Date</th>
                            @if (!$isClient)
                                <th class="text-left px-4 py-3 text-xs font-semibold text-outline">Logged by</th>
                            @endif
                            <th class="text-left px-4 py-3 text-xs font-semibold text-outline">Summary</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-outline">Duration</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-outline">Task</th>
                            @if (!$isClient)
                                <th class="text-left px-4 py-3 text-xs font-semibold text-outline">Status</th>
                            @endif
                            <th class="text-left px-4 py-3 text-xs font-semibold text-outline"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F1F5F9]">
                        @foreach ($timeLogs as $log)
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="px-4 py-3 text-xs font-medium text-on-surface whitespace-nowrap">
                                    {{ $log->log_date ? $log->log_date->format('d M Y') : '—' }}
                                </td>
                                @if (!$isClient)
                                    <td class="px-4 py-3 text-xs text-on-surface-variant">
                                        {{ $log->user->name ?? '—' }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-xs text-on-surface max-w-xs">
                                    @if ($isClient && $log->client_visible_summary)
                                        {{ Str::limit($log->client_visible_summary, 80) }}
                                    @else
                                        {{ Str::limit($log->work_summary, 80) }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-on-surface-variant whitespace-nowrap">
                                    {{ $log->durationForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-xs text-on-surface-variant">
                                    {{ $log->task ? Str::limit($log->task->title, 40) : '—' }}
                                </td>
                                @if (!$isClient)
                                    <td class="px-4 py-3">
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
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                              style="background-color:{{ $sc }}18; color:{{ $sc }};">
                                            {{ $log->statusLabel() }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('workspace.time-logs.show', [$workspace, $log]) }}"
                                       class="text-xs font-semibold hover:brightness-110 transition-all"
                                       style="color:#0058be;">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($timeLogs->hasPages())
                <div class="px-4 py-3 border-t border-[#E2E8F0]">
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
