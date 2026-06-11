<x-layouts.gvos title="My Workspaces">

    <div class="max-w-5xl mx-auto space-y-8">

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <x-portal.page-header
            title="My Workspaces"
            subtitle="All workspaces you are assigned to." />

        {{-- ── Empty state ──────────────────────────────────────────────── --}}
        @if ($workspaces->isEmpty())
            @php $__wsUser = auth()->user(); @endphp
            <div class="bg-white rounded-xl border border-border-subtle shadow-card">
                <x-portal.empty-state
                    icon="workspaces"
                    title="No workspaces yet"
                    :message="$__wsUser->hasAnyRole(['talent','line_manager'])
                        ? 'The GVOS operations team will create and assign your workspace when your engagement begins. You will receive an email notification once you are added.'
                        : ($__wsUser->hasAnyRole(['individual_client','business_client_admin','business_client_staff'])
                            ? 'Your workspace will be set up by the GVOS team. If you believe this is an error, please contact us.'
                            : 'Workspaces are created by the GVOS team when your service begins. Check back soon or contact support.')">
                    @if ($__wsUser->needsOnboarding())
                        <x-slot:action>
                            <a href="{{ route('onboarding.index') }}"
                               class="inline-flex items-center gap-2 bg-secondary text-on-secondary px-4 py-2 rounded-lg font-label-md text-label-md hover:brightness-110 transition-all">
                                <span class="material-symbols-outlined" style="font-size:16px">arrow_forward</span>
                                Complete your profile while you wait
                            </a>
                        </x-slot:action>
                    @endif
                </x-portal.empty-state>
            </div>

        {{-- ── Workspace cards ──────────────────────────────────────────── --}}
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach ($workspaces as $workspace)
                @php
                    $typeColors = [
                        'trial'   => 'bg-status-trial/10 text-status-trial border border-status-trial/20',
                        'ongoing' => 'bg-secondary/5 text-secondary border border-secondary/20',
                        'project' => 'bg-secondary/5 text-secondary border border-secondary/20',
                    ];
                    $typeCls   = $typeColors[$workspace->type]   ?? 'bg-surface-container-low text-on-surface-variant border border-border-subtle';
                @endphp
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="block bg-white rounded-xl border border-border-subtle shadow-sm hover:border-secondary/30 hover:shadow-card transition-all p-6 group card-lift">

                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-secondary/5 rounded-xl flex items-center justify-center group-hover:bg-secondary/10 transition-colors">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 20px;">workspaces</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $typeCls }}">
                                {{ ucfirst($workspace->type) }}
                            </span>
                            <x-portal.status-badge :status="$workspace->status" />
                        </div>
                    </div>

                    <h3 class="font-bold text-on-surface text-base leading-snug mb-1">{{ $workspace->name }}</h3>
                    <p class="text-xs text-outline mb-4">{{ $workspace->workspace_code }}</p>

                    @if ($workspace->description)
                        <p class="text-sm text-on-surface-variant mb-4 line-clamp-2">{{ $workspace->description }}</p>
                    @endif

                    <div class="flex items-center justify-between text-xs text-outline border-t border-border-subtle pt-3 mt-2">
                        <span>
                            @if ($workspace->primaryManager)
                                Manager: {{ $workspace->primaryManager->name }}
                            @elseif ($workspace->primaryTalent)
                                Talent: {{ $workspace->primaryTalent->name }}
                            @else
                                Team to be assigned
                            @endif
                        </span>
                        @if ($workspace->ends_at)
                            <span>Ends {{ $workspace->ends_at->format('d M Y') }}</span>
                        @elseif ($workspace->starts_at)
                            <span>Started {{ $workspace->starts_at->format('d M Y') }}</span>
                        @else
                            <span>Created {{ $workspace->created_at->format('d M Y') }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
        @endif

        {{-- ── Back link ─────────────────────────────────────────────────── --}}
        <div class="text-center pb-4">
            <a href="{{ auth()->user()->getDashboardRoute() }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center justify-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Back to Dashboard
            </a>
        </div>

    </div>

</x-layouts.gvos>
