<x-layouts.gvos title="My Workspaces">

    <div class="max-w-5xl mx-auto space-y-8">

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">My Workspaces</h2>
                <p class="text-sm text-slate-500 mt-1">All workspaces you are assigned to.</p>
            </div>
        </div>

        {{-- ── Empty state ──────────────────────────────────────────────── --}}
        @if ($workspaces->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-8 py-12 text-center">
                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-slate-700 mb-1">No workspaces yet</h3>
                <p class="text-sm text-slate-500">Workspaces are created by the GVOS team when your service begins.</p>
            </div>

        {{-- ── Workspace cards ──────────────────────────────────────────── --}}
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                @foreach ($workspaces as $workspace)
                @php
                    $statusColors = [
                        'active'    => 'bg-emerald-100 text-emerald-700',
                        'pending'   => 'bg-amber-100 text-amber-700',
                        'paused'    => 'bg-blue-100 text-blue-700',
                        'completed' => 'bg-slate-100 text-slate-600',
                        'cancelled' => 'bg-red-100 text-red-700',
                    ];
                    $typeColors = [
                        'trial'   => 'bg-violet-100 text-violet-700',
                        'ongoing' => 'bg-indigo-100 text-indigo-700',
                        'project' => 'bg-teal-100 text-teal-700',
                    ];
                    $statusCls = $statusColors[$workspace->status] ?? 'bg-slate-100 text-slate-600';
                    $typeCls   = $typeColors[$workspace->type] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="block bg-white rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all p-6 group">

                    <div class="flex items-start justify-between mb-4">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center group-hover:bg-indigo-100 transition-colors">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
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

                    <h3 class="font-bold text-slate-800 text-base leading-snug mb-1">{{ $workspace->name }}</h3>
                    <p class="text-xs text-slate-400 mb-4">{{ $workspace->workspace_code }}</p>

                    @if ($workspace->description)
                        <p class="text-sm text-slate-500 mb-4 line-clamp-2">{{ $workspace->description }}</p>
                    @endif

                    <div class="flex items-center justify-between text-xs text-slate-400 border-t border-slate-100 pt-3 mt-2">
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
               class="text-sm text-indigo-600 hover:text-indigo-500">
                ← Back to Dashboard
            </a>
        </div>

    </div>

</x-layouts.gvos>
