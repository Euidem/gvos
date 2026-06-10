<x-layouts.gvos :title="$workspace->name . ' — Weekly Report'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.reports.index', $workspace) }}" class="hover:text-secondary transition-colors">Weekly Reports</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>{{ $report->weekLabel() }}</span>
    </div>

    {{-- ── Session flash ─────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#065F46;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Main content ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Report header card --}}
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-bold text-on-surface flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">summarize</span>
                            {{ $report->weekLabel() }}
                        </h2>
                        <p class="text-xs text-outline mt-0.5">
                            {{ $report->totalDurationForHumans() }} logged
                            @if ($report->preparedBy)
                                &middot; Prepared by {{ $report->preparedBy->name }}
                            @endif
                            @if ($report->wasGenerated() && ! $isClient)
                                &middot; <span style="color:#7C3AED;" class="inline-flex items-center gap-0.5">
                                    <span class="material-symbols-outlined" style="font-size:12px;">auto_awesome</span>
                                    Auto-generated {{ $report->generated_at?->format('d M Y') }}
                                </span>
                            @endif
                        </p>
                    </div>
                    @php
                        $statusColors = [
                            'draft'     => '#94A3B8',
                            'submitted' => '#0058be',
                            'approved'  => '#7C3AED',
                            'published' => '#059669',
                        ];
                        $sc = $statusColors[$report->status] ?? '#94A3B8';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                          style="background-color:{{ $sc }}18; color:{{ $sc }};">
                        {{ $report->statusLabel() }}
                    </span>
                </div>

                {{-- Summary --}}
                <div class="mb-5">
                    <p class="text-xs font-semibold text-outline uppercase tracking-wide mb-2">
                        @if ($isClient) Week Summary @else Summary @endif
                    </p>
                    <p class="text-sm text-on-surface whitespace-pre-line leading-relaxed">{{ $report->summary }}</p>
                </div>

                {{-- Achievements --}}
                @if ($report->achievements)
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-outline uppercase tracking-wide mb-2">
                            @if ($isClient) Work Completed @else Achievements @endif
                        </p>
                        <p class="text-sm text-on-surface whitespace-pre-line leading-relaxed">{{ $report->achievements }}</p>
                    </div>
                @endif

                {{-- Hours summary block for clients --}}
                @if ($isClient && $report->total_minutes > 0)
                    @php
                        $ch = intdiv($report->total_minutes, 60);
                        $cm = $report->total_minutes % 60;
                    @endphp
                    <div class="mb-5 rounded-xl px-5 py-4"
                         style="background:rgba(0,88,190,0.04);border:1px solid rgba(0,88,190,0.12);">
                        <p class="text-xs font-semibold text-outline uppercase tracking-wide mb-1.5">Hours This Week</p>
                        <p class="text-2xl font-bold" style="color:#0058be;">
                            {{ $ch > 0 ? $ch . 'h' : '' }}{{ $cm > 0 ? ' ' . $cm . 'm' : '' }}
                        </p>
                        <p class="text-xs text-outline mt-0.5">Logged and approved by your GVOS team</p>
                    </div>
                @endif

                {{-- Blockers (internal only) --}}
                @if ($report->blockers && !$isClient)
                    <div class="mb-5 rounded-lg px-4 py-3"
                         style="background:rgba(217,119,6,0.04);border:1px solid rgba(217,119,6,0.12);">
                        <p class="text-xs font-semibold uppercase tracking-wide mb-2 flex items-center gap-1.5"
                           style="color:#D97706;">
                            <span class="material-symbols-outlined" style="font-size:13px;">lock</span>
                            Blockers <span class="font-normal normal-case tracking-normal" style="color:#92400E;">(internal)</span>
                        </p>
                        <p class="text-sm text-on-surface whitespace-pre-line">{{ $report->blockers }}</p>
                    </div>
                @endif

                {{-- Next steps (internal only) --}}
                @if ($report->next_steps && !$isClient)
                    <div class="mb-5 rounded-lg px-4 py-3"
                         style="background:rgba(217,119,6,0.04);border:1px solid rgba(217,119,6,0.12);">
                        <p class="text-xs font-semibold uppercase tracking-wide mb-2 flex items-center gap-1.5"
                           style="color:#D97706;">
                            <span class="material-symbols-outlined" style="font-size:13px;">lock</span>
                            Next Steps <span class="font-normal normal-case tracking-normal" style="color:#92400E;">(internal)</span>
                        </p>
                        <p class="text-sm text-on-surface whitespace-pre-line">{{ $report->next_steps }}</p>
                    </div>
                @endif

                {{-- Client notes --}}
                @if ($report->client_notes)
                    <div class="mb-5">
                        <p class="text-xs font-semibold text-outline uppercase tracking-wide mb-2">
                            @if ($isClient) Message from Your Team @else Client Notes @endif
                        </p>
                        <div class="rounded-xl px-4 py-3 text-sm text-on-surface whitespace-pre-line leading-relaxed"
                             style="background:rgba(5,150,105,0.04);border:1px solid rgba(5,150,105,0.15);">
                            {{ $report->client_notes }}
                        </div>
                    </div>
                @endif

                {{-- Client-view footer --}}
                @if ($isClient && $report->status === 'published')
                    <div class="pt-3 mt-2 border-t border-[#F1F5F9] flex items-center gap-2 text-xs text-outline">
                        <span class="material-symbols-outlined" style="font-size:14px;color:#059669;">verified</span>
                        Published {{ $report->published_at?->format('d M Y') }} by your GVOS team.
                        @if ($report->reviewedBy) Reviewed by {{ $report->reviewedBy->name }}. @endif
                    </div>
                @endif

                {{-- Action buttons --}}
                @if ($canEdit || $canApprove || $canPublish)
                    <div class="flex flex-wrap items-center gap-3 mt-2 pt-4 border-t border-[#F1F5F9]">
                        @if ($canEdit)
                            <a href="{{ route('workspace.reports.edit', [$workspace, $report]) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                               style="background-color:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                Edit Report
                            </a>
                        @endif

                        @if ($canApprove)
                            <form method="POST" action="{{ route('workspace.reports.update', [$workspace, $report]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="week_start_date" value="{{ $report->week_start_date->format('Y-m-d') }}">
                                <input type="hidden" name="week_end_date" value="{{ $report->week_end_date->format('Y-m-d') }}">
                                <input type="hidden" name="summary" value="{{ $report->summary }}">
                                <input type="hidden" name="total_minutes" value="{{ $report->total_minutes }}">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold border"
                                        style="border-color:#7C3AED;color:#7C3AED;">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
                                    Mark Approved
                                </button>
                            </form>
                        @endif

                        @if ($canPublish)
                            {{-- Phase 17: Dedicated publish action --}}
                            <form method="POST" action="{{ route('workspace.reports.publish', [$workspace, $report]) }}"
                                  onsubmit="return confirm('Publish this report to the client? They will be notified.')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                                        style="background-color:#059669;">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">publish</span>
                                    Publish to Client
                                </button>
                            </form>
                        @endif

                        @if (!$isClient && in_array($report->status, ['draft','submitted']) && $canEdit)
                            <form method="POST" action="{{ route('workspace.reports.destroy', [$workspace, $report]) }}"
                                  onsubmit="return confirm('Delete this report?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-semibold border"
                                        style="border-color:#DC2626;color:#DC2626;">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                {{-- Validation errors from publish --}}
                @if ($errors->has('publish'))
                    <div class="mt-3 p-3 rounded-lg text-xs"
                         style="background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.25);color:#991B1B;">
                        {{ $errors->first('publish') }}
                    </div>
                @endif
            </div>

        </div>

        {{-- ── Sidebar ───────────────────────────────────────────────────── --}}
        <div class="space-y-4">

            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
                <h3 class="text-xs font-bold text-outline uppercase tracking-wide mb-3">Report Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-outline">Week</dt>
                        <dd class="font-medium text-on-surface text-right">{{ $report->weekLabel() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-outline">Total Time</dt>
                        <dd class="font-medium text-on-surface">{{ $report->totalDurationForHumans() }}</dd>
                    </div>
                    @if ($report->preparedBy)
                        <div class="flex justify-between">
                            <dt class="text-outline">Prepared by</dt>
                            <dd class="font-medium text-on-surface">{{ $report->preparedBy->name }}</dd>
                        </div>
                    @endif
                    @if ($report->reviewedBy && !$isClient)
                        <div class="flex justify-between">
                            <dt class="text-outline">Reviewed by</dt>
                            <dd class="font-medium text-on-surface">{{ $report->reviewedBy->name }}</dd>
                        </div>
                    @endif
                    @if ($report->published_at)
                        <div class="flex justify-between">
                            <dt class="text-outline">Published</dt>
                            <dd class="font-medium text-on-surface">{{ $report->published_at->format('d M Y') }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-outline">Status</dt>
                        <dd>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                  style="background-color:{{ $sc }}18; color:{{ $sc }};">
                                {{ $report->statusLabel() }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Link to time logs for this week --}}
            @if (!$isClient)
                <a href="{{ route('workspace.time-logs.index', $workspace) }}"
                   class="flex items-center gap-2 text-sm hover:text-secondary transition-colors"
                   style="color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">schedule</span>
                    View Time Logs
                </a>
            @endif

            <a href="{{ route('workspace.reports.index', $workspace) }}"
               class="flex items-center gap-2 text-sm hover:text-secondary transition-colors"
               style="color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                All Reports
            </a>

        </div>
    </div>

</x-layouts.gvos>
