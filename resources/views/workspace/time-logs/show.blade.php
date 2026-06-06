<x-layouts.gvos :title="$workspace->name . ' — Time Log'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.time-logs.index', $workspace) }}" class="hover:text-secondary transition-colors">Time Logs</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>{{ $timeLog->log_date ? $timeLog->log_date->format('d M Y') : 'Log' }}</span>
    </div>

    {{-- ── Session flash ─────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#065F46;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Main content ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Work summary card --}}
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">schedule</span>
                            {{ $timeLog->log_date ? $timeLog->log_date->format('l, d M Y') : 'Time Log' }}
                        </h2>
                        <p class="text-xs text-outline mt-0.5">
                            Logged by {{ $timeLog->user->name ?? '—' }}
                            @if ($timeLog->durationForHumans() !== '—')
                                &middot; {{ $timeLog->durationForHumans() }}
                            @endif
                        </p>
                    </div>
                    @php
                        $statusColors = [
                            'draft'     => '#94A3B8',
                            'running'   => '#10B981',
                            'submitted' => '#0058be',
                            'reviewed'  => '#7C3AED',
                            'approved'  => '#059669',
                            'rejected'  => '#DC2626',
                        ];
                        $sc = $statusColors[$timeLog->status] ?? '#94A3B8';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                          style="background-color:{{ $sc }}18; color:{{ $sc }};">
                        {{ $timeLog->statusLabel() }}
                    </span>
                </div>

                <div class="space-y-4">
                    @if ($timeLog->isRunning() && !in_array($role, ['client_admin','client_staff','client']))
                        <div class="p-4 rounded-lg"
                             style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.24);">
                            <p class="text-xs font-bold uppercase tracking-wide" style="color:#059669;">Running session</p>
                            <p class="font-mono text-lg font-bold text-on-surface mt-1 js-running-timer"
                               data-started-at="{{ $timeLog->started_at?->toIso8601String() }}">
                                {{ $timeLog->durationForHumans() }}
                            </p>
                            <p class="text-xs text-on-surface-variant mt-1">
                                Started {{ $timeLog->started_at?->format('d M Y H:i') }}. Browser time is display-only; saved duration is calculated on the server when the timer stops.
                            </p>
                        </div>
                    @endif

                    {{-- Summary --}}
                    <div>
                        <p class="text-xs font-semibold text-outline uppercase tracking-wide mb-1">Work Summary</p>
                        <p class="text-sm text-on-surface">{{ $timeLog->work_summary }}</p>
                    </div>

                    {{-- Client visible summary (shown to clients, or to managers when visibility=client_summary) --}}
                    @if ($timeLog->client_visible_summary && !$canReview === false)
                        @if (!in_array($role, ['client_admin','client_staff','client']))
                            <div class="p-3 rounded-lg" style="background:rgba(0,88,190,0.04);border:1px solid rgba(0,88,190,0.12);">
                                <p class="text-xs font-semibold mb-1" style="color:#0058be;">Client-Visible Summary</p>
                                <p class="text-sm text-on-surface">{{ $timeLog->client_visible_summary }}</p>
                            </div>
                        @endif
                    @endif

                    {{-- Work details (internal only) --}}
                    @if ($timeLog->work_details && $canReview)
                        <div>
                            <p class="text-xs font-semibold text-outline uppercase tracking-wide mb-1">Work Details</p>
                            <p class="text-sm text-on-surface whitespace-pre-line">{{ $timeLog->work_details }}</p>
                        </div>
                    @elseif ($timeLog->work_details && !in_array($role, ['client_admin','client_staff','client']))
                        <div>
                            <p class="text-xs font-semibold text-outline uppercase tracking-wide mb-1">Work Details</p>
                            <p class="text-sm text-on-surface whitespace-pre-line">{{ $timeLog->work_details }}</p>
                        </div>
                    @endif

                    {{-- Manager notes (not shown to clients) --}}
                    @if ($timeLog->manager_notes && !in_array($role, ['client_admin','client_staff','client']))
                        <div class="p-3 rounded-lg" style="background:rgba(124,58,237,0.04);border:1px solid rgba(124,58,237,0.12);">
                            <p class="text-xs font-semibold mb-1" style="color:#7C3AED;">Manager Notes</p>
                            <p class="text-sm text-on-surface whitespace-pre-line">{{ $timeLog->manager_notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Action buttons --}}
                @if ($timeLog->isRunning() && $timeLog->canBeStoppedBy(auth()->user()))
                    <div class="mt-6 pt-4 border-t border-[#F1F5F9] space-y-3">
                        <form method="POST" action="{{ route('workspace.time-tracker.stop', $workspace) }}">
                            @csrf
                            <input type="hidden" name="time_log_id" value="{{ $timeLog->id }}">
                            <input type="hidden" name="status" value="draft">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold border"
                                    style="border-color:#F59E0B;color:#92400E;background:rgba(245,158,11,0.08);">
                                <span class="material-symbols-outlined" style="font-size: 16px;">stop_circle</span>
                                Clock Out
                            </button>
                        </form>
                        <form method="POST" action="{{ route('workspace.time-tracker.complete', $workspace) }}" class="space-y-2 max-w-xl">
                            @csrf
                            <input type="hidden" name="time_log_id" value="{{ $timeLog->id }}">
                            <input type="text" name="work_summary" required maxlength="1000"
                                   placeholder="Work summary for review"
                                   class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                                    style="background-color:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">task_alt</span>
                                Complete Work Session
                            </button>
                        </form>
                    </div>
                @elseif ($canEdit || $canReview)
                    <div class="flex items-center gap-3 mt-6 pt-4 border-t border-[#F1F5F9]">
                        @if ($canEdit)
                            <a href="{{ route('workspace.time-logs.edit', [$workspace, $timeLog]) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                               style="background-color:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                Edit
                            </a>
                        @endif

                        @if ($canReview && $timeLog->status === 'submitted')
                            <button onclick="document.getElementById('review-form').classList.toggle('hidden')"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold border"
                                    style="border-color:#7C3AED;color:#7C3AED;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">rate_review</span>
                                Review
                            </button>
                        @endif

                        @if ($canReview || ($timeLog->status === 'draft' && (int)$timeLog->user_id === (int)auth()->id()))
                            <form method="POST" action="{{ route('workspace.time-logs.destroy', [$workspace, $timeLog]) }}"
                                  onsubmit="return confirm('Delete this time log?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold border transition-all"
                                        style="border-color:#DC2626;color:#DC2626;">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

            {{-- ── Review form (manager / admin only, shown when submitted) ── --}}
            @if ($canReview && $timeLog->status === 'submitted')
                <div id="review-form" class="hidden bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                    <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">rate_review</span>
                        Review This Log
                    </h3>
                    <form method="POST" action="{{ route('workspace.time-logs.review', [$workspace, $timeLog]) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1">Decision <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-4">
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" name="action" value="approved" required> Approve
                                </label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" name="action" value="reviewed"> Mark Reviewed
                                </label>
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="radio" name="action" value="rejected"> Reject
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1">Manager Notes (optional)</label>
                            <textarea name="manager_notes" rows="3" maxlength="2000"
                                      class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y"
                                      placeholder="Internal feedback for this log…">{{ old('manager_notes', $timeLog->manager_notes) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1">Visibility</label>
                            <select name="visibility"
                                    class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                                <option value="internal" {{ $timeLog->visibility === 'internal' ? 'selected' : '' }}>Internal only</option>
                                <option value="client_summary" {{ $timeLog->visibility === 'client_summary' ? 'selected' : '' }}>Show summary to client</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface mb-1">Client-Visible Summary (if visibility = client summary)</label>
                            <textarea name="client_visible_summary" rows="2" maxlength="2000"
                                      class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be] resize-y"
                                      placeholder="What the client will see…">{{ old('client_visible_summary', $timeLog->client_visible_summary) }}</textarea>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-5 py-2 rounded-lg text-sm font-semibold text-white"
                                    style="background-color:#0058be;">
                                Submit Review
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>

        {{-- ── Sidebar metadata ─────────────────────────────────────────── --}}
        <div class="space-y-4">

            {{-- Details card --}}
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
                <h3 class="text-xs font-bold text-outline uppercase tracking-wide mb-3">Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-outline">Date</dt>
                        <dd class="font-medium text-on-surface">{{ $timeLog->log_date ? $timeLog->log_date->format('d M Y') : '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-outline">Duration</dt>
                        <dd class="font-medium text-on-surface {{ $timeLog->isRunning() ? 'js-running-timer font-mono' : '' }}"
                            @if ($timeLog->isRunning()) data-started-at="{{ $timeLog->started_at?->toIso8601String() }}" @endif>
                            {{ $timeLog->durationForHumans() }}
                        </dd>
                    </div>
                    @if ($timeLog->started_at && !in_array($role, ['client_admin','client_staff','client']))
                        <div class="flex justify-between">
                            <dt class="text-outline">Start</dt>
                            <dd class="font-medium text-on-surface">{{ $timeLog->started_at->format('H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-outline">End</dt>
                            <dd class="font-medium text-on-surface">{{ $timeLog->ended_at ? $timeLog->ended_at->format('H:i') : '—' }}</dd>
                        </div>
                    @endif
                    @if ($timeLog->task)
                        <div class="flex justify-between">
                            <dt class="text-outline">Task</dt>
                            <dd class="font-medium text-on-surface text-right max-w-[150px]">
                                <a href="{{ route('workspace.tasks.show', [$workspace, $timeLog->task]) }}"
                                   class="hover:text-secondary transition-colors" style="color:#0058be;">
                                    {{ $timeLog->task->task_code }}
                                </a>
                            </dd>
                        </div>
                    @endif
                    @if (!in_array($role, ['client_admin','client_staff','client']))
                        <div class="flex justify-between">
                            <dt class="text-outline">Visibility</dt>
                            <dd class="font-medium text-on-surface">{{ $timeLog->visibilityLabel() }}</dd>
                        </div>
                        @if ($timeLog->reviewedBy)
                            <div class="flex justify-between">
                                <dt class="text-outline">Reviewed by</dt>
                                <dd class="font-medium text-on-surface">{{ $timeLog->reviewedBy->name }}</dd>
                            </div>
                        @endif
                        @if ($timeLog->reviewed_at)
                            <div class="flex justify-between">
                                <dt class="text-outline">Reviewed at</dt>
                                <dd class="font-medium text-on-surface">{{ $timeLog->reviewed_at->format('d M Y') }}</dd>
                            </div>
                        @endif
                    @endif
                </dl>
            </div>

            {{-- Back link --}}
            <a href="{{ route('workspace.time-logs.index', $workspace) }}"
               class="flex items-center gap-2 text-sm transition-colors hover:text-secondary"
               style="color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                All Time Logs
            </a>

        </div>
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
