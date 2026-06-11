<x-layouts.gvos :title="$workspace->name . ' — Weekly Reports'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Weekly Reports</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">summarize</span>
                Weekly Reports
            </h2>
            <p class="text-xs text-outline mt-0.5">{{ $workspace->workspace_code }}
                @if ($isClient) &middot; Showing published reports @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if ($canCreate)
                <a href="{{ route('workspace.reports.generate', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold transition-all text-white"
                   style="background-color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">auto_awesome</span>
                    Generate Report
                </a>
                <a href="{{ route('workspace.reports.create', $workspace) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
                   style="border-color:#0058be; color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 14px;">edit_note</span>
                    Write Manually
                </a>
            @endif
            <a href="{{ route('workspace.time-logs.index', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#0058be; color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 14px;">schedule</span>
                Time Logs
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

    {{-- ── Reports list ─────────────────────────────────────────────────── --}}
    @if ($reports->isEmpty())
        <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-12 text-center">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mx-auto mb-4"
                 style="background-color:rgba(0,88,190,.06);">
                <span class="material-symbols-outlined" style="font-size: 26px; color:#0058be;">summarize</span>
            </div>
            <h4 class="text-sm font-semibold mb-1" style="color:#1E293B;">No reports yet</h4>
            <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                @if ($canCreate)
                    Create the first weekly report for this workspace.
                @elseif ($isClient)
                    Your manager will publish weekly progress reports here once your engagement is underway.
                @else
                    No reports have been created in this workspace yet.
                @endif
            </p>
            @if ($canCreate)
                <a href="{{ route('workspace.reports.generate', $workspace) }}"
                   class="mt-4 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold text-white"
                   style="background-color:#0058be;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">auto_awesome</span>
                    Generate First Report
                </a>
            @endif
        </div>
    @else
        <div class="space-y-3">
            @foreach ($reports as $report)
                @php
                    $statusColors = [
                        'draft'     => '#94A3B8',
                        'submitted' => '#0058be',
                        'approved'  => '#7C3AED',
                        'published' => '#059669',
                    ];
                    $sc = $statusColors[$report->status] ?? '#94A3B8';
                @endphp
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('workspace.reports.show', [$workspace, $report]) }}"
                               class="text-sm font-bold hover:text-secondary transition-colors"
                               style="color:#1E293B;">
                                {{ $report->weekLabel() }}
                            </a>
                            <p class="text-xs text-outline mt-0.5">
                                {{ $report->totalDurationForHumans() }} logged
                                @if ($report->preparedBy)
                                    &middot; Prepared by {{ $report->preparedBy->name }}
                                @endif
                                @if ($report->published_at)
                                    &middot; Published {{ $report->published_at->format('d M Y') }}
                                @endif
                                @if ($report->wasGenerated())
                                    &middot; <span title="Generated from workspace data" style="color:#7C3AED;">auto-generated</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  style="background-color:{{ $sc }}18; color:{{ $sc }};">
                                {{ $report->statusLabel() }}
                            </span>
                            <a href="{{ route('workspace.reports.show', [$workspace, $report]) }}"
                               class="text-xs font-semibold hover:brightness-110" style="color:#0058be;">
                                View →
                            </a>
                        </div>
                    </div>
                    @if ($report->summary)
                        <p class="text-xs text-on-surface-variant mt-2">{{ Str::limit($report->summary, 120) }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if ($reports->hasPages())
            <div class="mt-4">{{ $reports->links() }}</div>
        @endif
    @endif

</x-layouts.gvos>
