<x-layouts.gvos :title="$workspace->name . ' — Generate Report'">

    {{-- ── Breadcrumb ────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-5">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.reports.index', $workspace) }}" class="hover:text-secondary transition-colors">Weekly Reports</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span>Generate Draft</span>
    </div>

    {{-- ── Page header ─────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-bold text-on-surface flex items-center gap-2">
                <span class="material-symbols-outlined text-secondary" style="font-size: 22px;">auto_awesome</span>
                Generate Weekly Report Draft
            </h2>
            <p class="text-sm text-on-surface-variant mt-1">
                Select a date range. The system will build a draft from approved/submitted time logs and completed tasks.
                You can edit all sections before publishing.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('workspace.reports.create', $workspace) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
               style="border-color:#0058be; color:#0058be;">
                <span class="material-symbols-outlined" style="font-size: 14px;">edit_note</span>
                Write Manually
            </a>
            <a href="{{ route('workspace.reports.index', $workspace) }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Back
            </a>
        </div>
    </div>

    {{-- ── Validation errors ─────────────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="mb-4 p-4 rounded-lg text-sm"
             style="background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.25);color:#991B1B;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Left: date form + preview counts ───────────────────────────── --}}
        <div class="lg:col-span-1 space-y-5">

            {{-- Date selection card --}}
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                <h3 class="text-sm font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">date_range</span>
                    Select Week
                </h3>

                {{-- Preview form (GET to refresh counts) --}}
                <form method="GET" action="{{ route('workspace.reports.generate', $workspace) }}"
                      class="space-y-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full px-3 py-2 rounded-lg border border-[#E2E8F0] text-sm focus:outline-none focus:ring-2 focus:ring-[#0058be]">
                    </div>
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition-all"
                            style="border-color:#0058be; color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size:14px;">refresh</span>
                        Refresh Preview
                    </button>
                </form>

                {{-- Generate button (POST) --}}
                <form method="POST" action="{{ route('workspace.reports.generate.store', $workspace) }}"
                      onsubmit="return (document.getElementById('gen-approved').textContent * 1 + document.getElementById('gen-submitted').textContent * 1) > 0 || confirm('No time logs found for this range. Generate an empty draft anyway?')">
                    @csrf
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date"   value="{{ $endDate }}">
                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110"
                            style="background-color:#0058be;">
                        <span class="material-symbols-outlined" style="font-size:16px;">auto_awesome</span>
                        Generate Draft Report
                    </button>
                </form>
            </div>

            {{-- How it works --}}
            <div class="rounded-xl border border-secondary/15 bg-secondary/5 p-4 text-sm space-y-2">
                <p class="font-semibold text-secondary flex items-center gap-1.5">
                    <span class="material-symbols-outlined" style="font-size:16px;">info</span>
                    What gets included
                </p>
                <ul class="text-on-surface-variant space-y-1.5 text-xs">
                    <li class="flex items-start gap-1.5">
                        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size:13px;">check</span>
                        Approved time logs (hours and session count)
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size:13px;">check</span>
                        Submitted time logs (manager draft context only)
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size:13px;">check</span>
                        Completed tasks (approved + closed)
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size:13px;">check</span>
                        Blocked tasks
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="material-symbols-outlined text-secondary flex-shrink-0 mt-0.5" style="font-size:13px;">check</span>
                        Active / pending tasks (next steps)
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="material-symbols-outlined" style="font-size:13px; color:#EF4444;">close</span>
                        <span class="text-status-blocked">Internal manager notes and work details are excluded</span>
                    </li>
                </ul>
            </div>

        </div>

        {{-- ── Right: preview counts ────────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                <h3 class="text-sm font-bold text-on-surface mb-5 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size:18px;">preview</span>
                    Preview for
                    <span class="text-secondary">
                        {{ \Illuminate\Support\Carbon::parse($startDate)->format('j M') }}
                        – {{ \Illuminate\Support\Carbon::parse($endDate)->format('j M Y') }}
                    </span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">

                    {{-- Approved logs --}}
                    <div class="rounded-xl border border-border-subtle bg-surface-container-low p-4 text-center">
                        <p class="text-2xl font-bold text-on-surface" id="gen-approved">{{ $preview['approved_log_count'] }}</p>
                        <p class="text-xs text-outline mt-1">Approved sessions</p>
                        @php
                            $ah = intdiv($preview['total_approved_min'], 60);
                            $am = $preview['total_approved_min'] % 60;
                        @endphp
                        @if ($preview['total_approved_min'] > 0)
                            <p class="text-xs font-semibold mt-1" style="color:#0058be;">
                                {{ $ah > 0 ? $ah . 'h ' : '' }}{{ $am > 0 ? $am . 'm' : '' }} approved
                            </p>
                        @endif
                    </div>

                    {{-- Submitted logs --}}
                    <div class="rounded-xl border border-border-subtle bg-surface-container-low p-4 text-center">
                        <p class="text-2xl font-bold text-on-surface" id="gen-submitted">{{ $preview['submitted_log_count'] }}</p>
                        <p class="text-xs text-outline mt-1">Pending review</p>
                        @php
                            $sh = intdiv($preview['total_submitted_min'] ?? 0, 60);
                            $sm = ($preview['total_submitted_min'] ?? 0) % 60;
                        @endphp
                        @if (($preview['total_submitted_min'] ?? 0) > 0)
                            <p class="text-xs font-semibold mt-1" style="color:#94A3B8;">
                                {{ $sh > 0 ? $sh . 'h ' : '' }}{{ $sm > 0 ? $sm . 'm' : '' }} submitted
                            </p>
                        @endif
                    </div>

                    {{-- Completed tasks --}}
                    <div class="rounded-xl border border-border-subtle bg-surface-container-low p-4 text-center">
                        <p class="text-2xl font-bold text-on-surface">{{ $preview['completed_task_count'] }}</p>
                        <p class="text-xs text-outline mt-1">Tasks completed</p>
                    </div>

                    {{-- Blocked tasks --}}
                    <div class="rounded-xl border border-border-subtle bg-surface-container-low p-4 text-center">
                        <p class="text-2xl font-bold {{ $preview['blocked_task_count'] > 0 ? '' : 'text-on-surface' }}"
                           style="{{ $preview['blocked_task_count'] > 0 ? 'color:#EF4444' : '' }}">
                            {{ $preview['blocked_task_count'] }}
                        </p>
                        <p class="text-xs text-outline mt-1">Blocked tasks</p>
                    </div>

                    {{-- Total time for manager --}}
                    @php
                        $mh = intdiv($preview['total_manager_min'], 60);
                        $mm = $preview['total_manager_min'] % 60;
                        $totalLabel = ($mh > 0 ? $mh . 'h' : '') . ($mm > 0 ? ' ' . $mm . 'm' : '');
                    @endphp
                    <div class="rounded-xl border border-secondary/20 bg-secondary/5 p-4 text-center sm:col-span-2">
                        <p class="text-2xl font-bold" style="color:#0058be;">{{ $preview['total_manager_min'] > 0 ? trim($totalLabel) : '—' }}</p>
                        <p class="text-xs mt-1" style="color:#0058be;">Total time (approved + submitted)</p>
                        <p class="text-xs text-outline mt-0.5">Only approved hours appear in the client summary</p>
                    </div>

                </div>

                {{-- Status messages --}}
                @if ($preview['approved_log_count'] === 0 && $preview['submitted_log_count'] === 0)
                    <div class="rounded-lg border border-status-payment-due/20 bg-status-payment-due/5 p-3 flex items-start gap-2 text-sm">
                        <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:16px; color:#F59E0B;">warning</span>
                        <span class="text-on-surface-variant">No time logs found for this date range. The draft will be created with placeholder content — you can fill in the details manually.</span>
                    </div>
                @elseif ($preview['approved_log_count'] === 0 && $preview['submitted_log_count'] > 0)
                    <div class="rounded-lg border border-secondary/20 bg-secondary/5 p-3 flex items-start gap-2 text-sm">
                        <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:16px; color:#0058be;">info</span>
                        <span class="text-on-surface-variant">{{ $preview['submitted_log_count'] }} submitted log(s) found but none approved yet. The client hours summary will show 0h until time logs are approved.</span>
                    </div>
                @else
                    <div class="rounded-lg border border-status-active/20 bg-status-active/5 p-3 flex items-start gap-2 text-sm">
                        <span class="material-symbols-outlined flex-shrink-0 mt-0.5" style="font-size:16px; color:#10B981;">check_circle</span>
                        <span class="text-on-surface-variant">Ready to generate. Click "Generate Draft Report" to create a draft from this data.</span>
                    </div>
                @endif

            </div>
        </div>

    </div>

</x-layouts.gvos>
