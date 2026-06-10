<x-layouts.gvos title="My Workspaces">

    <div class="max-w-5xl mx-auto space-y-8">

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">My Workspaces</h2>
                <p class="text-sm text-on-surface-variant mt-1">All workspaces you are assigned to.</p>
            </div>
        </div>

        {{-- ── Empty state ──────────────────────────────────────────────── --}}
        @if ($workspaces->isEmpty())
            @php $__wsUser = auth()->user(); @endphp
            <div class="bg-white rounded-xl border border-border-subtle shadow-card px-8 py-12 text-center">
                <div class="w-14 h-14 bg-surface-container-low rounded-xl flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-outline" style="font-size: 28px;">workspaces</span>
                </div>
                <h3 class="text-base font-semibold text-on-surface mb-2">No workspaces yet</h3>
                @if ($__wsUser->hasAnyRole(['talent','line_manager']))
                    <p class="text-sm text-on-surface-variant max-w-sm mx-auto">
                        The GVOS operations team will create and assign your workspace when your engagement begins.
                        You will receive an email notification once you are added.
                    </p>
                @elseif ($__wsUser->hasAnyRole(['individual_client','business_client_admin','business_client_staff']))
                    <p class="text-sm text-on-surface-variant max-w-sm mx-auto">
                        Your workspace will be set up by the GVOS team. If you believe this is an error,
                        please contact us.
                    </p>
                @else
                    <p class="text-sm text-on-surface-variant max-w-sm mx-auto">
                        Workspaces are created by the GVOS team when your service begins.
                        Check back soon or contact support.
                    </p>
                @endif
                @if ($__wsUser->needsOnboarding())
                    <a href="{{ route('onboarding.index') }}"
                       class="mt-4 inline-flex items-center gap-2 bg-secondary text-on-secondary px-4 py-2 rounded-lg text-sm font-semibold hover:brightness-110 transition-all">
                        <span class="material-symbols-outlined" style="font-size:16px">arrow_forward</span>
                        Complete your profile while you wait
                    </a>
                @endif
            </div>

        {{-- ── Workspace cards ──────────────────────────────────────────── --}}
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach ($workspaces as $workspace)
                @php
                    $statusColors = [
                        'active'    => 'bg-status-active/10 text-status-active border border-status-active/20',
                        'pending'   => 'bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20',
                        'paused'    => 'bg-secondary/5 text-secondary border border-secondary/20',
                        'completed' => 'bg-status-completed/10 text-status-completed border border-status-completed/20',
                        'cancelled' => 'bg-status-blocked/10 text-status-blocked border border-status-blocked/20',
                    ];
                    $typeColors = [
                        'trial'   => 'bg-status-trial/10 text-status-trial border border-status-trial/20',
                        'ongoing' => 'bg-secondary/5 text-secondary border border-secondary/20',
                        'project' => 'bg-secondary/5 text-secondary border border-secondary/20',
                    ];
                    $statusCls = $statusColors[$workspace->status] ?? 'bg-surface-container-low text-on-surface-variant border border-border-subtle';
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
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusCls }}">
                                {{ ucfirst($workspace->status) }}
                            </span>
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
