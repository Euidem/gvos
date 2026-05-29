<x-layouts.gvos :title="$workspace->name">

    <div class="max-w-4xl mx-auto space-y-8">

        @php
            $statusColors = [
                'active'    => 'bg-emerald-100 text-emerald-700',
                'pending'   => 'bg-amber-100 text-amber-700',
                'paused'    => 'bg-blue-100 text-blue-700',
                'completed' => 'bg-slate-100 text-slate-600',
                'cancelled' => 'bg-red-100 text-red-700',
            ];
            $statusCls = $statusColors[$workspace->status] ?? 'bg-slate-100 text-slate-600';
        @endphp

        {{-- ── Page header ──────────────────────────────────────────────── --}}
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-2xl font-bold text-slate-800">{{ $workspace->name }}</h2>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusCls }}">
                        {{ ucfirst($workspace->status) }}
                    </span>
                </div>
                <p class="text-sm text-slate-500">{{ $workspace->workspace_code }} &middot; {{ ucfirst($workspace->type) }} workspace</p>
            </div>
            <a href="{{ route('workspace.index') }}"
               class="text-sm text-indigo-600 hover:text-indigo-500 mt-1">
                ← All Workspaces
            </a>
        </div>

        {{-- ── Status message ───────────────────────────────────────────── --}}
        @if ($workspace->status === 'pending')
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800">Workspace pending activation</p>
                    <p class="text-xs text-amber-700 mt-0.5">The GVOS team will activate this workspace when your engagement begins.</p>
                </div>
            </div>
        @elseif ($workspace->status === 'active')
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-5 py-4 flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-emerald-800">Workspace is active</p>
                    @if ($workspace->ends_at)
                        <p class="text-xs text-emerald-700 mt-0.5">
                            Ends {{ $workspace->ends_at->format('d M Y \a\t H:i') }}
                            @php
                                $hoursLeft = max(0, now()->floatDiffInHours($workspace->ends_at, false));
                            @endphp
                            &mdash; {{ number_format($hoursLeft, 1) }} hours remaining.
                        </p>
                    @endif
                </div>
            </div>
        @elseif ($workspace->status === 'completed')
            <div class="bg-slate-50 border border-slate-200 rounded-xl px-5 py-4">
                <p class="text-sm font-semibold text-slate-700">Workspace completed</p>
                <p class="text-xs text-slate-500 mt-0.5">This workspace has concluded. Contact the GVOS team to discuss next steps.</p>
            </div>
        @endif

        {{-- ── Details grid ──────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Team card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Your Team
                </h3>
                <div class="space-y-3">
                    @if ($workspace->primaryManager)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs">
                                {{ strtoupper(substr($workspace->primaryManager->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $workspace->primaryManager->name }}</p>
                                <p class="text-xs text-slate-500">Manager</p>
                            </div>
                        </div>
                    @endif
                    @if ($workspace->primaryTalent)
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 font-bold text-xs">
                                {{ strtoupper(substr($workspace->primaryTalent->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $workspace->primaryTalent->name }}</p>
                                <p class="text-xs text-slate-500">Talent</p>
                            </div>
                        </div>
                    @endif
                    @if (! $workspace->primaryManager && ! $workspace->primaryTalent)
                        <p class="text-sm text-slate-400 italic">Team to be assigned</p>
                    @endif
                </div>
            </div>

            {{-- Schedule card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Schedule
                </h3>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status</span>
                        <span class="font-semibold text-slate-800 capitalize">{{ $workspace->status }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Type</span>
                        <span class="font-semibold text-slate-800 capitalize">{{ $workspace->type }}</span>
                    </div>
                    @if ($workspace->starts_at)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Started</span>
                            <span class="font-semibold text-slate-800">{{ $workspace->starts_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    @if ($workspace->ends_at)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Ends</span>
                            <span class="font-semibold text-slate-800">{{ $workspace->ends_at->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                    @if ($workspace->task_limit > 0)
                        <div class="flex justify-between">
                            <span class="text-slate-500">Task limit</span>
                            <span class="font-semibold text-slate-800">{{ $workspace->task_limit }}</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── Members ───────────────────────────────────────────────────── --}}
        @if ($workspace->activeMembers->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-700 mb-4">All Workspace Members</h3>
                <div class="divide-y divide-slate-100">
                    @foreach ($workspace->activeMembers as $member)
                        <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-slate-100 rounded-full flex items-center justify-center text-slate-600 font-bold text-xs">
                                    {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $member->user->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-slate-500">{{ $member->user->email ?? '' }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ match($member->role) {
                                    'manager' => 'bg-indigo-100 text-indigo-700',
                                    'talent'  => 'bg-emerald-100 text-emerald-700',
                                    'client'  => 'bg-amber-100 text-amber-700',
                                    default   => 'bg-slate-100 text-slate-600',
                                } }}">
                                {{ ucfirst($member->role) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ── Coming soon placeholder ───────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="text-center py-6">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <h4 class="text-sm font-semibold text-slate-700 mb-1">Tasks, files, and chat coming soon</h4>
                <p class="text-xs text-slate-400">The GVOS team is setting up your full workspace. Task board and file sharing will be available in a future update.</p>
            </div>
        </div>

        {{-- ── Back link ─────────────────────────────────────────────────── --}}
        <div class="text-center pb-4">
            <a href="{{ route('workspace.index') }}"
               class="text-sm text-indigo-600 hover:text-indigo-500">
                ← Back to Workspaces
            </a>
        </div>

    </div>

</x-layouts.gvos>
