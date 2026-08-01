{{-- Phase 28 — Individual client overview. Progress first, approvals first among
     actions, billing only when it needs attention. No internal operations language. --}}
<x-layouts.gvos title="Overview">

@php
    $first     = auth()->user()->profile?->first_name ?? explode(' ', auth()->user()->name)[0];
    $primary   = $workspaces->first();
    $needsYou  = $awaitingApproval->count();
    $money     = fn ($amt) => ($billing['currency'] ?? 'USD') . ' ' . number_format((float) $amt, 2);
@endphp

@php $__obUser = auth()->user(); @endphp
@include('partials.onboarding-banner')

{{-- Billing restriction / overdue is stated once, at the top, and nowhere else. --}}
@if ($primary)
    @php
        $__billingWorkspace = $primary;
        $__billingForClient = true;
    @endphp
    @include('partials.billing-banner')
@endif

<x-portal.page-header
    title="Hello, {{ $first }}"
    :subtitle="$needsYou > 0
        ? $needsYou . ' ' . Str::plural('item', $needsYou) . ' ' . ($needsYou === 1 ? 'needs' : 'need') . ' your approval.'
        : ($workspaces->isEmpty()
            ? 'Your workspace is being set up. You will see progress here once it starts.'
            : 'Your team is working. Nothing needs your approval right now.')"
    :divider="false" />

@if (session('success'))
    <x-portal.alert type="success">{{ session('success') }}</x-portal.alert>
@elseif (session('error'))
    <x-portal.alert type="error">{{ session('error') }}</x-portal.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

    <div class="lg:col-span-2 space-y-5">

        {{-- 1. What needs the client --}}
        @if ($needsYou > 0)
            <x-portal.section title="Waiting for your approval" flush>
                <div class="divide-y divide-border-subtle">
                    @foreach ($awaitingApproval as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$t->workspace_id, $t])"
                            :title="$t->title"
                            :meta="'Completed by ' . ($t->assignedTo?->name ?? 'your team') . ($t->submitted_at ? ' · ' . $t->submitted_at->diffForHumans() : '')"
                            icon="rate_review"
                            tone="warn"
                            action="Review" />
                    @endforeach
                </div>
            </x-portal.section>
        @endif

        {{-- 2. Progress --}}
        <x-portal.section title="Latest progress report">
            @if ($latestReport)
                <p class="text-[12px] text-outline mb-1">
                    {{ $latestReport->weekLabel() }} · published {{ $latestReport->published_at?->format('j M Y') }}
                </p>
                <p class="text-[14px] text-on-surface leading-relaxed max-w-[720px]">
                    {{ Str::limit($latestReport->summary, 320) }}
                </p>
                <div class="flex items-center gap-2 mt-4">
                    <x-portal.btn variant="primary" icon="summarize"
                                  :href="route('workspace.reports.show', [$latestReport->workspace_id, $latestReport])">
                        Read Full Report
                    </x-portal.btn>
                    <x-portal.btn variant="secondary" :href="route('workspace.reports.index', $latestReport->workspace_id)">
                        All Reports
                    </x-portal.btn>
                </div>
            @else
                <x-portal.empty-state
                    compact
                    icon="summarize"
                    title="No report published yet"
                    message="Your team publishes a progress report each week. The first one will appear here." />
            @endif
        </x-portal.section>

        {{-- 3. What is being worked on --}}
        @if ($inProgress->isNotEmpty())
            <x-portal.section title="Being worked on now" flush>
                <div class="divide-y divide-border-subtle">
                    @foreach ($inProgress as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$t->workspace_id, $t])"
                            :title="$t->title"
                            :meta="$t->due_date ? 'Expected ' . $t->due_date->format('j M') : 'In progress'"
                            :tone="$t->status === 'in_progress' ? 'info' : 'default'"
                            :badge="$t->status === 'in_progress' ? 'In progress' : 'Not started'"
                            action="View" />
                    @endforeach
                </div>
            </x-portal.section>
        @endif
    </div>

    {{-- Right column --}}
    <div class="space-y-5">

        <x-portal.section title="Your workspace" flush>
            @if ($workspaces->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="workspaces"
                    title="Not set up yet"
                    message="Your GVOS account manager is preparing your workspace." />
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($workspaces as $w)
                        <x-portal.workspace-row
                            :workspace="$w"
                            :meta="$w->primaryTalent ? 'Your specialist · ' . $w->primaryTalent->name : null" />
                    @endforeach
                </div>
            @endif
        </x-portal.section>

        {{-- Billing: a single line unless something is owed --}}
        @if ($primary)
            <x-portal.section title="Billing">
                @if ($billing['outstanding'] > 0)
                    <p class="text-[13px] text-on-surface-variant">Outstanding balance</p>
                    <p class="font-headline-md text-[22px] font-bold mt-1"
                       style="color:{{ $billing['overdue'] ? '#B91C1C' : '#92400E' }};">
                        {{ $money($billing['outstanding']) }}
                    </p>
                    @if ($billing['invoice']?->due_date)
                        <p class="text-[12.5px] text-on-surface-variant mt-1">
                            {{ $billing['overdue'] ? 'Was due' : 'Due' }} {{ $billing['invoice']->due_date->format('j M Y') }}
                        </p>
                    @endif
                    <x-portal.btn variant="secondary" icon="receipt_long" class="mt-4 w-full"
                                  :href="route('workspace.billing.index', $primary)">
                        View Billing
                    </x-portal.btn>
                @else
                    <p class="text-[13.5px] text-on-surface-variant">Nothing outstanding.</p>
                    <a href="{{ route('workspace.billing.index', $primary) }}"
                       class="inline-flex items-center gap-1 mt-3 text-[12.5px] font-semibold text-secondary hover:underline">
                        View invoices
                        <span class="material-symbols-outlined" style="font-size:15px;">chevron_right</span>
                    </a>
                @endif
            </x-portal.section>
        @endif

        @if ($latestMessage)
            <x-portal.section title="From your team">
                <p class="text-[12px] text-outline mb-1.5">{{ $latestMessage->user?->name }}</p>
                <p class="text-[13.5px] text-on-surface-variant leading-relaxed">
                    {{ Str::limit($latestMessage->message, 170) }}
                </p>
                <a href="{{ route('workspace.chat.index', $latestMessage->workspace_id) }}"
                   class="inline-flex items-center gap-1 mt-3 text-[12.5px] font-semibold text-secondary hover:underline">
                    Reply
                    <span class="material-symbols-outlined" style="font-size:15px;">chevron_right</span>
                </a>
            </x-portal.section>
        @endif

    </div>
</div>

@if ($workspaces->isNotEmpty())
    <x-portal.metric-strip :metrics="[
        $needsYou > 0 ? ['label' => 'Needs your approval', 'value' => $needsYou, 'tone' => 'warn'] : null,
        ['label' => 'Being worked on', 'value' => $inProgress->count(), 'tone' => 'muted'],
        ['label' => 'Reports published', 'value' => $latestReport ? 'Latest ' . $latestReport->week_end_date?->format('j M') : 'None yet', 'tone' => 'muted'],
    ]" />
@endif

</x-layouts.gvos>
