{{-- Phase 28 — Business account admin overview. Company framing, approvals first,
     billing stated once. No internal operations language. --}}
<x-layouts.gvos title="Company Overview">

@php
    $first     = auth()->user()->profile?->first_name ?? explode(' ', auth()->user()->name)[0];
    $primary   = $workspaces->first();
    $needsYou  = $awaitingApproval->count();
    $money     = fn ($amt) => ($billing['currency'] ?? 'USD') . ' ' . number_format((float) $amt, 2);
    $orgName   = $company?->name ?? 'Your account';
@endphp

@php $__obUser = auth()->user(); @endphp
@include('partials.onboarding-banner')

@if ($primary)
    @php
        $__billingWorkspace = $primary;
        $__billingForClient = true;
    @endphp
    @include('partials.billing-banner')
@endif

<x-portal.page-header
    :title="$orgName"
    :subtitle="$needsYou > 0
        ? 'Hello ' . $first . '. ' . $needsYou . ' ' . Str::plural('item', $needsYou) . ' ' . ($needsYou === 1 ? 'needs' : 'need') . ' your approval.'
        : 'Hello ' . $first . '. ' . $workspaces->count() . ' active ' . Str::plural('workspace', $workspaces->count()) . ', nothing waiting on you.'"
    :divider="false" />

@if (session('success'))
    <x-portal.alert type="success">{{ session('success') }}</x-portal.alert>
@elseif (session('error'))
    <x-portal.alert type="error">{{ session('error') }}</x-portal.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

    <div class="lg:col-span-2 space-y-5">

        @if ($needsYou > 0)
            <x-portal.section title="Waiting for your approval" flush>
                <div class="divide-y divide-border-subtle">
                    @foreach ($awaitingApproval as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$t->workspace_id, $t])"
                            :title="$t->title"
                            :meta="$t->workspace?->name . ' · completed by ' . ($t->assignedTo?->name ?? 'your team')"
                            icon="rate_review"
                            tone="warn"
                            action="Review" />
                    @endforeach
                </div>
            </x-portal.section>
        @endif

        <x-portal.section title="Latest progress report">
            @if ($latestReport)
                <p class="text-[12px] text-outline mb-1">
                    {{ $latestReport->workspace?->name }} · {{ $latestReport->weekLabel() }}
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
                    message="Progress reports from your GVOS team appear here each week." />
            @endif
        </x-portal.section>

        <x-portal.section title="Your workspaces" flush>
            @if ($workspaces->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="workspaces"
                    title="No workspaces yet"
                    message="Your GVOS account manager is setting up your first workspace." />
            @else
                <div class="divide-y divide-border-subtle">
                    @foreach ($workspaces as $w)
                        <x-portal.workspace-row
                            :workspace="$w"
                            :meta="$w->primaryTalent ? 'Specialist · ' . $w->primaryTalent->name : null" />
                    @endforeach
                </div>
            @endif
        </x-portal.section>
    </div>

    <div class="space-y-5">

        @if ($primary)
            <x-portal.section title="Billing">
                @if ($billing['outstanding'] > 0)
                    <p class="text-[13px] text-on-surface-variant">Outstanding balance</p>
                    <p class="font-headline-md text-[22px] font-bold mt-1"
                       style="color:{{ $billing['overdue'] ? '#B91C1C' : '#92400E' }};">
                        {{ $money($billing['outstanding']) }}
                    </p>
                    @if ($billing['invoice'])
                        <p class="text-[12.5px] text-on-surface-variant mt-1">
                            Invoice {{ $billing['invoice']->invoice_number }}
                            @if ($billing['invoice']->due_date)
                                · {{ $billing['overdue'] ? 'was due' : 'due' }} {{ $billing['invoice']->due_date->format('j M Y') }}
                            @endif
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

            <x-portal.section title="Team access">
                <p class="text-[13.5px] text-on-surface-variant">
                    {{ $teamCount }} {{ Str::plural('person', $teamCount) }} on this account.
                </p>
                <a href="{{ route('workspace.members.index', $primary) }}"
                   class="inline-flex items-center gap-1 mt-3 text-[12.5px] font-semibold text-secondary hover:underline">
                    Manage who has access
                    <span class="material-symbols-outlined" style="font-size:15px;">chevron_right</span>
                </a>
            </x-portal.section>
        @endif

        @if ($latestMessage)
            <x-portal.section title="From your team">
                <p class="text-[12px] text-outline mb-1.5">
                    {{ $latestMessage->user?->name }} · {{ $latestMessage->workspace?->name }}
                </p>
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
        ['label' => 'Workspaces', 'value' => $workspaces->count()],
        $needsYou > 0 ? ['label' => 'Needs your approval', 'value' => $needsYou, 'tone' => 'warn'] : null,
        ['label' => 'Being worked on', 'value' => $inProgress->count(), 'tone' => 'muted'],
        ['label' => 'People with access', 'value' => $teamCount, 'tone' => 'muted'],
    ]" />
@endif

</x-layouts.gvos>
