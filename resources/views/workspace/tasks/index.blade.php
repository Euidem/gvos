<x-layouts.gvos :title="$workspace->name . ' — Task Board'">

@php
    $statusCols = [
        'pending'            => ['label' => 'Pending',            'icon' => 'pending_actions', 'color' => 'text-status-payment-due', 'bg' => 'bg-status-payment-due/10', 'border' => 'border-status-payment-due/20'],
        'in_progress'        => ['label' => 'In Progress',        'icon' => 'play_circle',     'color' => 'text-secondary',          'bg' => 'bg-secondary/10',           'border' => 'border-secondary/20'],
        'blocked'            => ['label' => 'Blocked',            'icon' => 'block',           'color' => 'text-status-blocked',     'bg' => 'bg-status-blocked/10',      'border' => 'border-status-blocked/20'],
        'submitted'          => ['label' => 'Submitted',          'icon' => 'upload',          'color' => 'text-status-trial',       'bg' => 'bg-status-trial/10',        'border' => 'border-status-trial/20'],
        'revision_requested' => ['label' => 'Revision Requested', 'icon' => 'edit_note',       'color' => 'text-status-urgent',      'bg' => 'bg-status-blocked/10',      'border' => 'border-status-blocked/20'],
        'approved'           => ['label' => 'Approved',           'icon' => 'check_circle',    'color' => 'text-status-active',      'bg' => 'bg-status-active/10',       'border' => 'border-status-active/20'],
        'closed'             => ['label' => 'Closed',             'icon' => 'task_alt',        'color' => 'text-status-completed',   'bg' => 'bg-status-completed/10',    'border' => 'border-status-completed/20'],
    ];

    $priorityBadge = [
        'low'    => ['class' => 'bg-surface-container text-on-surface-variant',                  'label' => 'Low'],
        'normal' => ['class' => 'bg-secondary/5 text-secondary',                                 'label' => 'Normal'],
        'high'   => ['class' => 'bg-status-payment-due/10 text-status-payment-due',              'label' => 'High'],
        'urgent' => ['class' => 'bg-status-blocked/10 text-status-blocked font-bold',            'label' => 'URGENT'],
    ];
@endphp

    {{-- ── Page header ──────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-1">
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span>Task Board</span>
            </div>
            <h2 class="text-xl font-bold text-on-surface">Task Board</h2>
            <p class="text-xs text-outline mt-0.5">{{ $tasks->count() }} task{{ $tasks->count() !== 1 ? 's' : '' }} &middot; {{ $workspace->workspace_code }}</p>
        </div>
        <div class="flex items-center gap-3">
            @if ($canCreate)
                <a href="{{ route('workspace.tasks.create', $workspace) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all"
                   style="background-color:#0058be">
                    <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                    New Task
                </a>
            @endif
            <a href="{{ route('workspace.show', $workspace) }}"
               class="text-sm text-secondary hover:brightness-110 transition-all flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                Workspace
            </a>
        </div>
    </div>

    {{-- ── Session messages ──────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-status-active/10 border border-status-active/20 rounded-lg text-sm text-status-active">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Task board — horizontal scrollable columns ─────────────────────── --}}
    <div class="overflow-x-auto pb-4">
        <div class="flex gap-4 min-w-max">

            @foreach ($statusCols as $statusKey => $col)
            @php
                $colTasks = $tasksByStatus->get($statusKey, collect());
            @endphp
            <div class="w-72 flex-shrink-0">

                {{-- Column header --}}
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined {{ $col['color'] }}" style="font-size: 18px;">{{ $col['icon'] }}</span>
                        <span class="text-xs font-bold text-on-surface uppercase tracking-wider">{{ $col['label'] }}</span>
                    </div>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $col['bg'] }} {{ $col['color'] }}">
                        {{ $colTasks->count() }}
                    </span>
                </div>

                {{-- Task cards --}}
                <div class="space-y-3">
                    @forelse ($colTasks as $task)
                    <a href="{{ route('workspace.tasks.show', [$workspace, $task]) }}"
                       class="block bg-white rounded-xl border border-[#E2E8F0] shadow-sm hover:shadow-md hover:border-[#0058be]/30 transition-all p-4 group">

                        {{-- Priority + code --}}
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $priorityBadge[$task->priority]['class'] ?? '' }}">
                                {{ $priorityBadge[$task->priority]['label'] ?? ucfirst($task->priority) }}
                            </span>
                            <span class="text-[10px] text-outline font-mono">{{ $task->task_code }}</span>
                        </div>

                        {{-- Title --}}
                        <p class="text-sm font-semibold text-[#191c1e] leading-snug mb-3 group-hover:text-[#0058be] transition-colors">
                            {{ Str::limit($task->title, 80) }}
                        </p>

                        {{-- Meta row --}}
                        <div class="flex items-center justify-between text-[11px] text-[#76777d]">
                            <div class="flex items-center gap-1.5">
                                @if ($task->assignedTo)
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0"
                                         style="background-color:#0058be">
                                        {{ strtoupper(substr($task->assignedTo->name, 0, 1)) }}
                                    </div>
                                    <span>{{ Str::limit($task->assignedTo->name, 14) }}</span>
                                @else
                                    <span class="italic">Unassigned</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($task->comments_count > 0)
                                    <span class="flex items-center gap-0.5">
                                        <span class="material-symbols-outlined" style="font-size: 13px;">chat_bubble</span>
                                        {{ $task->comments_count }}
                                    </span>
                                @endif
                                @if ($task->due_date)
                                    <span class="flex items-center gap-0.5 {{ $task->isOverdue() ? 'text-status-blocked font-semibold' : ($task->isDueSoon() ? 'text-status-payment-due font-semibold' : '') }}">
                                        <span class="material-symbols-outlined" style="font-size: 13px;">calendar_today</span>
                                        {{ $task->due_date->format('d M') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                    </a>
                    @empty
                    <div class="bg-white rounded-xl border border-dashed border-[#E2E8F0] p-4 text-center opacity-60">
                        <p class="text-xs text-[#76777d]">No tasks here</p>
                    </div>
                    @endforelse
                </div>
            </div>
            @endforeach

        </div>
    </div>

    {{-- ── Empty state (no tasks at all) ─────────────────────────────────── --}}
    @if ($tasks->isEmpty())
        <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-12 text-center mt-4">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-4" style="background-color:rgba(0,88,190,.05)">
                <span class="material-symbols-outlined" style="font-size: 28px; color:#0058be;">task_alt</span>
            </div>
            <h4 class="text-sm font-semibold text-[#191c1e] mb-1">No tasks yet</h4>
            <p class="text-xs text-[#76777d] max-w-xs mx-auto mb-4">
                Tasks help track work inside this workspace. Create the first task to get started.
            </p>
            @if ($canCreate)
                <a href="{{ route('workspace.tasks.create', $workspace) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all"
                   style="background-color:#0058be">
                    <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                    Create First Task
                </a>
            @endif
        </div>
    @endif

</x-layouts.gvos>
