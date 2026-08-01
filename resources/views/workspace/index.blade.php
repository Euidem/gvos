{{-- Phase 28 — Workspaces list. A scannable list, not a card grid: the user is
     choosing where to go, so name + status + team is all that is needed. --}}
<x-layouts.gvos title="Workspaces">

    <x-portal.page-header
        title="Workspaces"
        :subtitle="$workspaces->count() . ' ' . Str::plural('workspace', $workspaces->count()) . ' you can open.'" />

    @if ($workspaces->isEmpty())
        @php $__wsUser = auth()->user(); @endphp
        <div class="bg-white rounded-xl border border-border-subtle shadow-card">
            <x-portal.empty-state
                icon="workspaces"
                title="No workspaces yet"
                :message="$__wsUser->hasAnyRole(['talent','line_manager'])
                    ? 'The GVOS operations team creates and assigns your workspace when your engagement begins. You will be notified once you are added.'
                    : ($__wsUser->hasAnyRole(['individual_client','business_client_admin','business_client_staff'])
                        ? 'Your GVOS team is setting up your workspace. It will appear here as soon as it is ready.'
                        : 'Workspaces are created by the GVOS team when your service begins.')">
                @if ($__wsUser->needsOnboarding())
                    <x-slot:action>
                        <x-portal.btn variant="primary" icon="arrow_forward" :href="route('onboarding.index')">
                            Finish Setting Up Your Profile
                        </x-portal.btn>
                    </x-slot:action>
                @endif
            </x-portal.empty-state>
        </div>
    @else
        <div class="bg-white rounded-xl border border-border-subtle shadow-card overflow-hidden">
            <div class="divide-y divide-border-subtle">
                @foreach ($workspaces as $workspace)
                    @php
                        $meta = $workspace->primaryManager
                            ? 'Manager · ' . $workspace->primaryManager->name
                            : ($workspace->primaryTalent ? 'Specialist · ' . $workspace->primaryTalent->name : 'Team to be assigned');

                        $alerts = [];
                        if ($workspace->type === 'trial') {
                            $alerts[] = ['label' => 'Trial', 'tone' => 'info'];
                        }
                        if ($workspace->status !== 'active') {
                            $alerts[] = ['label' => $workspace->statusLabel(), 'tone' => 'warn'];
                        }
                    @endphp
                    <x-portal.workspace-row
                        :workspace="$workspace"
                        :meta="$meta"
                        :alerts="$alerts" />
                @endforeach
            </div>
        </div>
    @endif

</x-layouts.gvos>
