<x-layouts.gvos :title="$workspace->name . ' — Weekly Reports'">
{{-- Stitch reference: weekly_report_gvos/code.html — Phase 26 Batch 3 --}}

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-1">
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
                <span class="material-symbols-outlined" style="font-size:14px;">chevron_right</span>
                <span>Weekly Reports</span>
            </div>
            <h2 class="font-headline-md text-headline-md text-on-surface font-bold flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size:22px;">summarize</span>
                Weekly Reports
            </h2>
            <p class="font-label-md text-label-md text-outline mt-0.5">
                {{ $workspace->workspace_code }}
                @if ($isClient) &middot; Showing published reports only @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($canCreate)
                <a href="{{ route('workspace.reports.generate', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                   style="background-color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size:16px;">auto_awesome</span>
                    Generate Report
                </a>
                <a href="{{ route('workspace.reports.create', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
                   style="border-color:#0058be;color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size:14px;">edit_note</span>
                    Write Manually
                </a>
            @endif
            <a href="{{ route('workspace.time-logs.index', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#0058be;color:#0058be;">
                <span class="material-symbols-outlined" style="font-size:14px;">schedule</span>
                Time Logs
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

    {{-- ── Reports list ─────────────────────────────────────────────────── --}}
    @if ($reports->isEmpty())
        <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-12 text-center">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                 style="background:rgba(0,88,190,0.06);">
                <span class="material-symbols-outlined text-secondary" style="font-size:26px;">summarize</span>
            </div>
            <h4 class="font-label-md text-label-md font-semibold text-on-surface mb-1">No reports yet</h4>
            <p class="font-label-md text-[11px] text-outline max-w-xs mx-auto">
                @if ($canCreate)
                    Generate the first weekly report from workspace activity data.
                @elseif ($isClient)
                    Your manager will publish weekly progress reports here once your engagement is underway.
                @else
                    No reports have been created in this workspace yet.
                @endif
            </p>
            @if ($canCreate)
                <div class="flex flex-wrap items-center justify-center gap-3 mt-5">
                    <a href="{{ route('workspace.reports.generate', $workspace) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                       style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size:16px;">auto_awesome</span>
                        Generate First Report
                    </a>
                    <a href="{{ route('workspace.reports.create', $workspace) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border"
                       style="border-color:#0058be;color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size:14px;">edit_note</span>
                        Write Manually
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="space-y-3">
            @foreach ($reports as $report)
                @php
                    $statusColors = [
                        'draft'     => ['color' => '#94A3B8', 'bg' => '#F8FAFC'],
                        'submitted' => ['color' => '#0058be', 'bg' => '#EFF6FF'],
                        'approved'  => ['color' => '#7C3AED', 'bg' => '#F5F3FF'],
                        'published' => ['color' => '#059669', 'bg' => '#ECFDF5'],
                    ];
                    $sc = $statusColors[$report->status] ?? ['color' => '#94A3B8', 'bg' => '#F8FAFC'];
                @endphp
                <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                    {{-- Card header --}}
                    <div class="flex items-start justify-between px-5 pt-4 pb-3 border-b border-border-subtle"
                         style="background:{{ $sc['bg'] }};">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0"
                                 style="background:{{ $sc['color'] }}18;">
                                <span class="material-symbols-outlined" style="font-size:18px;color:{{ $sc['color'] }};">summarize</span>
                            </div>
                            <div>
                                <a href="{{ route('workspace.reports.show', [$workspace, $report]) }}"
                                   class="font-label-md text-label-md font-bold text-on-surface hover:text-secondary transition-colors">
                                    {{ $report->weekLabel() }}
                                </a>
                                <p class="font-label-md text-[11px] text-outline mt-0.5">
                                    {{ $report->totalDurationForHumans() }} logged
                                    @if ($report->preparedBy)
                                        &middot; Prepared by {{ $report->preparedBy->name }}
                                    @endif
                                    @if ($report->wasGenerated())
                                        &middot; <span style="color:#7C3AED;">auto-generated</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-label-md text-[10px] font-semibold"
                                  style="background:{{ $sc['color'] }}18;color:{{ $sc['color'] }};">
                                {{ $report->statusLabel() }}
                            </span>
                        </div>
                    </div>
                    {{-- Card body --}}
                    <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            @if ($report->summary)
                                <p class="font-label-md text-label-md text-on-surface-variant">{{ Str::limit($report->summary, 140) }}</p>
                            @else
                                <p class="font-label-md text-label-md text-outline italic">No summary added.</p>
                            @endif
                            @if ($report->published_at)
                                <p class="font-label-md text-[11px] text-outline mt-1">
                                    <span class="material-symbols-outlined align-middle" style="font-size:11px;">publish</span>
                                    Published {{ $report->published_at->format('d M Y') }}
                                </p>
                            @endif
                        </div>
                        <a href="{{ route('workspace.reports.show', [$workspace, $report]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-label-md text-[11px] font-semibold border flex-shrink-0 transition-all hover:brightness-110"
                           style="border-color:{{ $sc['color'] }};color:{{ $sc['color'] }};">
                            <span class="material-symbols-outlined" style="font-size:13px;">open_in_new</span>
                            View Report
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($reports->hasPages())
            <div class="mt-5">{{ $reports->links() }}</div>
        @endif
    @endif

</x-layouts.gvos>
