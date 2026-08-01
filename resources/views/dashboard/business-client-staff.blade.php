{{-- Phase 28 — Business client staff / observer overview. Deliberately simpler:
     no billing, no vault, no member administration. When the user is an observer
     in every workspace, approval affordances are hidden entirely. --}}
<x-layouts.gvos title="Overview">

@php
    $first    = auth()->user()->profile?->first_name ?? explode(' ', auth()->user()->name)[0];
    $needsYou = $isObserver ? 0 : $awaitingApproval->count();
@endphp

@php $__obUser = auth()->user(); @endphp
@include('partials.onboarding-banner')

<x-portal.page-header
    title="Hello, {{ $first }}"
    :subtitle="$isObserver
        ? 'You have view-only access to your team\'s workspaces.'
        : ($needsYou > 0
            ? $needsYou . ' ' . Str::plural('item', $needsYou) . ' ' . ($needsYou === 1 ? 'needs' : 'need') . ' your review.'
            : ($workspaces->isEmpty()
                ? 'You have not been added to a workspace yet.'
                : 'Nothing needs your attention right now.'))"
    :divider="false" />

@if (session('success'))
    <x-portal.alert type="success">{{ session('success') }}</x-portal.alert>
@elseif (session('error'))
    <x-portal.alert type="error">{{ session('error') }}</x-portal.alert>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

    <div class="lg:col-span-2 space-y-5">

        {{-- Observers never see an approval call to action. --}}
        @if ($needsYou > 0)
            <x-portal.section title="Waiting for your review" flush>
                <div class="divide-y divide-border-subtle">
                    @foreach ($awaitingApproval as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$t->workspace_id, $t])"
                            :title="$t->title"
                            :meta="$t->workspace?->name . ' · completed by ' . ($t->assignedTo?->name ?? 'the team')"
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
                    {{ Str::limit($latestReport->summary, 300) }}
                </p>
                <x-portal.btn variant="primary" icon="summarize" class="mt-4"
                              :href="route('workspace.reports.show', [$latestReport->workspace_id, $latestReport])">
                    Read Full Report
                </x-portal.btn>
            @else
                <x-portal.empty-state
                    compact
                    icon="summarize"
                    title="No report published yet"
                    message="Weekly progress reports appear here once your GVOS team publishes the first one." />
            @endif
        </x-portal.section>

        @if ($inProgress->isNotEmpty())
            <x-portal.section title="Being worked on now" flush>
                <div class="divide-y divide-border-subtle">
                    @foreach ($inProgress as $t)
                        <x-portal.attention-item
                            :href="route('workspace.tasks.show', [$t->workspace_id, $t])"
                            :title="$t->title"
                            :meta="$t->workspace?->name . ($t->due_date ? ' · expected ' . $t->due_date->format('j M') : '')"
                            :tone="$t->status === 'in_progress' ? 'info' : 'default'"
                            :badge="$t->status === 'in_progress' ? 'In progress' : 'Not started'"
                            action="View" />
                    @endforeach
                </div>
            </x-portal.section>
        @endif
    </div>

    <div class="space-y-5">
        <x-portal.section title="Your workspaces" flush>
            @if ($workspaces->isEmpty())
                <x-portal.empty-state
                    compact
                    icon="workspaces"
                    title="No access yet"
                    message="Your account administrator will give you access to a workspace." />
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
                    {{ $isObserver ? 'Open messages' : 'Reply' }}
                    <span class="material-symbols-outlined" style="font-size:15px;">chevron_right</span>
                </a>
            </x-portal.section>
        @endif
    </div>
</div>

@if ($workspaces->isNotEmpty())
    <x-portal.metric-strip :metrics="[
        ['label' => 'Workspaces', 'value' => $workspaces->count()],
        $needsYou > 0 ? ['label' => 'Needs your review', 'value' => $needsYou, 'tone' => 'warn'] : null,
        ['label' => 'Being worked on', 'value' => $inProgress->count(), 'tone' => 'muted'],
    ]" />
@endif

</x-layouts.gvos>
