{{-- Phase 28 — Workspace overview. Answers: what is this, what is happening,
     what needs me, who is involved, what next. Module navigation lives in the
     workspace tab bar in the shell, so this page no longer repeats it. --}}
<x-layouts.gvos :title="$workspace->name">

@php
    $open      = $taskCounts->only(['pending', 'in_progress', 'blocked', 'submitted', 'revision_requested'])->sum();
    $done      = $taskCounts->only(['approved', 'closed'])->sum();
    $blocked   = (int) $taskCounts->get('blocked', 0);
    $submitted = (int) $taskCounts->get('submitted', 0);

    $attentionTitle = match (true) {
        $internal => 'Needs your attention',
        $isClient => 'Waiting for your approval',
        default   => 'Your work here',
    };
@endphp

<x-portal.page-header
    :title="$workspace->name"
    :subtitle="$workspace->description"
    :badge="$workspace->statusLabel()"
    :badge-type="$workspace->isActive() ? 'success' : 'neutral'"
    eyebrow="All workspaces"
    :eyebrow-href="route('workspace.index')">
    <x-slot:actions>
        @if ($canCreateTask)
            <x-portal.btn variant="primary" icon="add" :href="route('workspace.tasks.create', $workspace)">
                New Task
            </x-portal.btn>
        @else
            <x-portal.btn variant="primary" icon="task_alt" :href="route('workspace.tasks.index', $workspace)">
                Open Task Board
            </x-portal.btn>
        @endif
    </x-slot:actions>
</x-portal.page-header>

@if (session('success'))
    <x-portal.alert type="success">{{ session('success') }}</x-portal.alert>
@elseif (session('error'))
    <x-portal.alert type="error">{{ session('error') }}</x-portal.alert>
@endif

{{-- Billing state is stated once, here, and nowhere else on this page. --}}
@php
    $__billingWorkspace = $workspace;
    $__billingForClient = $isClient;
@endphp
@include('partials.billing-banner')

@if ($workspace->status !== 'active')
    <x-portal.alert type="warning" title="This workspace is {{ strtolower($workspace->statusLabel()) }}">
        Some actions may be unavailable until it is active again.
    </x-portal.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

    {{-- ── What needs me ────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        <x-portal.section :title="$attentionTitle" flush>
            @if ($needsAttention->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="check_circle"
                    title="Nothing needs attention"
                    :message="$isClient
                        ? 'Your team will let you know when something is ready for you to review.'
                        : 'All work here is up to date.'" />
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($needsAttention as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$workspace, $t])"
                            :title="$t->title"
                            :meta="($t->assignedTo?->name ?? 'Unassigned') . ($t->due_date ? ' · due ' . $t->due_date->format('j M') : '')"
                            :badge="$t->statusLabel()"
                            :tone="$t->status === 'blocked' ? 'urgent' : ($t->status === 'submitted' ? 'warn' : 'info')"
                            :action="$t->status === 'submitted' && ($internal || $isClient) ? 'Review' : 'Open'" />
                    @endforeach
                </div>
            @endif
        </x-portal.section>

        {{-- Progress at a glance — a caption strip, not six cards --}}
        <x-portal.section title="Progress" :card="false">
            <div class="bg-white rounded-xl border border-border-subtle shadow-card p-5">
                <x-portal.metric-strip :metrics="[
                    ['label' => 'Open', 'value' => $open],
                    ['label' => 'Completed', 'value' => $done, 'tone' => 'good'],
                    $blocked > 0 ? ['label' => 'Blocked', 'value' => $blocked, 'tone' => 'urgent'] : null,
                    $submitted > 0 ? ['label' => 'Awaiting review', 'value' => $submitted, 'tone' => 'warn'] : null,
                ]" />
                @if ($open + $done > 0)
                    @php $pct = (int) round($done / max(1, $open + $done) * 100); @endphp
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-[12px] text-outline mb-1.5">
                            <span>{{ $pct }}% complete</span>
                            <a href="{{ route('workspace.tasks.index', $workspace) }}"
                               class="font-semibold text-secondary hover:underline">Open board</a>
                        </div>
                        <div class="w-full h-1.5 rounded-full" style="background:rgba(0,88,190,0.08);">
                            <div class="h-full rounded-full" style="width:{{ $pct }}%;background:#0058be;"></div>
                        </div>
                    </div>
                @endif
            </div>
        </x-portal.section>

        {{-- Latest report --}}
        @if ($latestReport)
            <x-portal.section title="Latest report">
                <p class="text-[12px] text-outline mb-1">
                    {{ $latestReport->weekLabel() }} · {{ $latestReport->statusLabel() }}
                </p>
                <p class="text-[14px] text-on-surface leading-relaxed max-w-[720px]">
                    {{ Str::limit($latestReport->summary, 260) }}
                </p>
                <a href="{{ route('workspace.reports.show', [$workspace, $latestReport]) }}"
                   class="inline-flex items-center gap-1 mt-3 text-[12.5px] font-semibold text-secondary hover:underline">
                    Read the full report
                    <span class="material-symbols-outlined" style="font-size:15px;">chevron_right</span>
                </a>
            </x-portal.section>
        @endif
    </div>

    {{-- ── Who is involved + recent conversation ────────────────────────── --}}
    <div class="space-y-5">

        <x-portal.section title="Who is involved" flush>
            <x-slot:actions>
                <a href="{{ route('workspace.members.index', $workspace) }}"
                   class="text-[12.5px] font-semibold text-secondary hover:underline">All</a>
            </x-slot:actions>
            <div class="divide-y divide-border-subtle">
                @foreach ($workspace->activeMembers->take(6) as $m)
                    <div class="flex items-center gap-3 px-4 py-2.5">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold text-white flex-shrink-0"
                              style="background-color:#0058be;">
                            {{ strtoupper(substr($m->user?->name ?? '?', 0, 1)) }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[13.5px] font-medium text-on-surface truncate">{{ $m->user?->name }}</span>
                            <span class="block text-[11.5px] text-outline">{{ $m->roleLabel() }}</span>
                        </span>
                    </div>
                @endforeach
                @if ($workspace->activeMembers->isEmpty())
                    <x-portal.empty-state compact icon="group" title="No members yet"
                        message="Team members appear here once they are added." />
                @endif
            </div>
        </x-portal.section>

        <x-portal.section title="Recent messages" flush>
            <x-slot:actions>
                <a href="{{ route('workspace.chat.index', $workspace) }}"
                   class="text-[12.5px] font-semibold text-secondary hover:underline">Open</a>
            </x-slot:actions>
            @if ($latestMessages->isEmpty())
                <x-portal.empty-state compact icon="forum" title="No messages yet"
                    message="Start the conversation with your team." />
            @else
                {{-- Previews, not links: the section's "Open" action is the single
                     route to the chat page, so rows must not repeat that href. --}}
                <div class="divide-y divide-border-subtle">
                    @foreach ($latestMessages as $msg)
                        <div class="px-4 py-3">
                            <p class="text-[12px] text-outline mb-0.5">
                                {{ $msg->user?->name }} · {{ $msg->created_at?->diffForHumans() }}
                                @if ($msg->visibility === 'internal')
                                    <span class="ml-1 text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                          style="color:#92400E;background:rgba(245,158,11,0.12);">Internal</span>
                                @endif
                            </p>
                            <p class="text-[13px] text-on-surface-variant leading-snug">{{ Str::limit($msg->message, 110) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-portal.section>

        <x-portal.section title="Details">
            <dl class="space-y-2.5">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-[12.5px] text-outline">Reference</dt>
                    <dd class="font-mono-sm text-[12px] text-on-surface">{{ $workspace->workspace_code }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-[12.5px] text-outline">Type</dt>
                    <dd class="text-[12.5px] text-on-surface">{{ \App\Models\Workspace::typeLabels()[$workspace->type] ?? $workspace->type }}</dd>
                </div>
                @if ($workspace->starts_at)
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[12.5px] text-outline">Started</dt>
                        <dd class="text-[12.5px] text-on-surface">{{ $workspace->starts_at->format('j M Y') }}</dd>
                    </div>
                @endif
                @if ($workspace->company)
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-[12.5px] text-outline">Account</dt>
                        <dd class="text-[12.5px] text-on-surface truncate">{{ $workspace->company->name }}</dd>
                    </div>
                @endif
            </dl>
        </x-portal.section>

    </div>
</div>

</x-layouts.gvos>
