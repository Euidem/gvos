<x-layouts.gvos :title="$task->task_code . ' — ' . $task->title">

@php
    $statusCls = match($task->status) {
        'pending'            => 'bg-status-payment-due/10 text-status-payment-due border border-status-payment-due/20',
        'in_progress'        => 'bg-secondary/10 text-secondary border border-secondary/20',
        'blocked'            => 'bg-status-blocked/10 text-status-blocked border border-status-blocked/20',
        'submitted'          => 'bg-status-trial/10 text-status-trial border border-status-trial/20',
        'revision_requested' => 'bg-status-blocked/10 text-status-blocked border border-status-blocked/20',
        'approved'           => 'bg-status-active/10 text-status-active border border-status-active/20',
        'closed'             => 'bg-status-completed/10 text-status-completed border border-status-completed/20',
        'cancelled'          => 'bg-surface-container text-on-surface-variant border border-border-subtle',
        default              => 'bg-surface-container text-on-surface-variant border border-border-subtle',
    };

    $priorityCls = match($task->priority) {
        'low'    => 'bg-surface-container text-on-surface-variant',
        'normal' => 'bg-secondary/5 text-secondary',
        'high'   => 'bg-status-payment-due/10 text-status-payment-due',
        'urgent' => 'bg-status-blocked/10 text-status-blocked font-bold',
        default  => 'bg-surface-container text-on-surface-variant',
    };

    $statusLabels = \App\Models\WorkspaceTask::statusLabels();
    $statusIcons  = [
        'pending'            => 'pending_actions',
        'in_progress'        => 'play_circle',
        'blocked'            => 'block',
        'submitted'          => 'upload',
        'revision_requested' => 'edit_note',
        'approved'           => 'check_circle',
        'closed'             => 'task_alt',
        'cancelled'          => 'cancel',
    ];

    $accentColor = match($task->status) {
        'in_progress'        => '#0058be',
        'blocked',
        'revision_requested' => '#EF4444',
        'submitted'          => '#7C3AED',
        'approved'           => '#059669',
        'closed'             => '#6B7280',
        default              => '#D97706',
    };
@endphp

    {{-- ── Page header ────────────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-3">
            <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
            <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
            <a href="{{ route('workspace.tasks.index', $workspace) }}" class="hover:text-secondary transition-colors">Kanban Board</a>
            <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
            <span class="font-mono text-outline">{{ $task->task_code }}</span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusCls }}">
                        {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $priorityCls }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                </div>
                <h1 class="font-headline-lg text-headline-lg text-primary leading-tight">{{ $task->title }}</h1>
            </div>
            @if ($canEdit)
                <a href="{{ route('workspace.tasks.edit', [$workspace, $task]) }}"
                   class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border border-border-subtle text-on-surface-variant hover:border-secondary/20 transition-all mt-1">
                    <span class="material-symbols-outlined" style="font-size: 15px;">edit</span>
                    Edit Task
                </a>
            @endif
        </div>
    </div>

    {{-- ── Session messages ────────────────────────────────────────────────── --}}
    @if (session('success'))
        <x-portal.alert type="success" class="mb-4">{{ session('success') }}</x-portal.alert>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-start gap-3 px-4 py-3 rounded-xl text-sm"
             style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.20);color:#991B1B;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">error</span>
            <div>
                {{ session('error') }}
                @if (session('active_timer_url'))
                    <a href="{{ session('active_timer_url') }}" class="font-semibold underline ml-1">View active timer</a>
                @endif
            </div>
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-4 rounded-xl" style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.20);">
            @foreach ($errors->all() as $error)
                <p class="text-xs" style="color:#991B1B;">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl">

        {{-- ── Main column ──────────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Task body card --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="h-[3px] w-full" style="background:{{ $accentColor }};"></div>
                <div class="p-6">
                    @if ($task->description)
                        <div class="prose prose-sm max-w-none text-on-surface-variant text-sm leading-relaxed whitespace-pre-wrap">{{ $task->description }}</div>
                    @else
                        <p class="text-sm italic text-outline">No description provided.</p>
                    @endif

                    @if ($isAdminOrManager && $task->internal_notes)
                        <div class="mt-5 p-3.5 rounded-lg" style="background:rgba(0,88,190,0.04);border:1px solid rgba(0,88,190,0.15);">
                            <p class="text-[11px] font-semibold text-secondary uppercase tracking-wider mb-1.5">Internal Notes</p>
                            <p class="text-xs text-on-surface-variant whitespace-pre-wrap">{{ $task->internal_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Status transitions ──────────────────────────────────────── --}}
            @if (! empty($allowedTransitions))
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm p-5">
                <h3 class="text-xs font-semibold text-outline uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 15px;">swap_horiz</span>
                    Move Task
                </h3>
                <form method="POST" action="{{ route('workspace.tasks.status.update', [$workspace, $task]) }}" class="flex flex-wrap gap-2">
                    @csrf
                    @foreach ($allowedTransitions as $nextStatus)
                    @php
                        $btnCls = match($nextStatus) {
                            'in_progress'        => 'border-secondary/30 text-secondary hover:bg-secondary/5',
                            'submitted'          => 'border-status-trial/30 text-status-trial hover:bg-status-trial/5',
                            'blocked'            => 'border-status-blocked/30 text-status-blocked hover:bg-status-blocked/5',
                            'approved'           => 'border-status-active/30 text-status-active hover:bg-status-active/5',
                            'closed'             => 'border-status-completed/30 text-status-completed hover:bg-status-completed/5',
                            'revision_requested' => 'border-status-blocked/30 text-status-blocked hover:bg-status-blocked/5',
                            'pending'            => 'border-border-subtle text-on-surface-variant hover:bg-surface-container-low',
                            'cancelled'          => 'border-status-blocked/30 text-status-blocked hover:bg-status-blocked/5',
                            default              => 'border-border-subtle text-on-surface-variant hover:bg-surface-container-low',
                        };
                    @endphp
                    <button type="submit" name="status" value="{{ $nextStatus }}"
                            onclick="return confirm('Move task to: {{ $statusLabels[$nextStatus] ?? $nextStatus }}?')"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition-all {{ $btnCls }}">
                        <span class="material-symbols-outlined" style="font-size: 14px;">{{ $statusIcons[$nextStatus] ?? 'circle' }}</span>
                        {{ $statusLabels[$nextStatus] ?? ucfirst($nextStatus) }}
                    </button>
                    @endforeach
                </form>
            </div>
            @endif

            {{-- ── Comments ─────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-border-subtle flex items-center gap-2"
                     style="background:rgba(247,249,251,1);">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">chat_bubble</span>
                    <h3 class="text-sm font-bold text-on-surface">Comments</h3>
                    <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded-full ml-1"
                          style="background:rgba(0,88,190,0.08);color:#0058be;">{{ $comments->count() }}</span>
                </div>

                <div class="divide-y divide-border-subtle">
                    @forelse ($comments as $comment)
                    <div class="px-6 py-4 {{ $comment->isInternal() ? 'border-l-2' : '' }}"
                         style="{{ $comment->isInternal() ? 'border-left-color:#0058be;background:rgba(0,88,190,0.02);' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5"
                                 style="{{ $comment->isInternal() ? 'background:#0058be;' : 'background:#2170e4;' }}">
                                {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="text-xs font-semibold text-on-surface">{{ $comment->user->name ?? 'Unknown' }}</span>
                                    @if ($comment->isInternal())
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded"
                                              style="background:rgba(0,88,190,0.10);color:#0058be;">Internal</span>
                                    @endif
                                    <span class="text-[11px] text-outline">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-on-surface-variant whitespace-pre-wrap leading-relaxed">{{ $comment->comment }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm italic text-outline">No comments yet. Be the first to add one.</p>
                    </div>
                    @endforelse
                </div>

                {{-- Add comment form --}}
                <div class="px-6 py-5 border-t border-border-subtle" style="background:rgba(247,249,251,0.5);">
                    <form method="POST" action="{{ route('workspace.tasks.comments.store', [$workspace, $task]) }}" class="space-y-3">
                        @csrf
                        <textarea name="comment" rows="3"
                                  placeholder="Add a comment…"
                                  required maxlength="5000"
                                  class="w-full px-4 py-3 border border-border-subtle rounded-lg text-sm text-on-surface bg-white focus:outline-none focus:ring-2 focus:ring-secondary/20 focus:border-secondary resize-y @error('comment') border-status-blocked @enderror">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="text-xs text-status-blocked">{{ $message }}</p>
                        @enderror
                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:brightness-110 flex items-center gap-2"
                                    style="background:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 15px;">send</span>
                                Post Comment
                            </button>
                            @if ($isAdminOrManager)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="visibility" value="internal"
                                           class="h-4 w-4 rounded border-border-subtle text-secondary"
                                           {{ old('visibility') === 'internal' ? 'checked' : '' }}>
                                    <span class="text-xs text-on-surface-variant">Internal only</span>
                                </label>
                            @else
                                <input type="hidden" name="visibility" value="public">
                            @endif
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- ── Sidebar column ────────────────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Task meta --}}
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border-subtle" style="background:rgba(247,249,251,1);">
                    <h3 class="text-xs font-semibold text-outline uppercase tracking-wider">Task Details</h3>
                </div>
                <dl class="divide-y divide-border-subtle text-sm">
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Status</dt>
                        <dd>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusCls }}">
                                {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Priority</dt>
                        <dd>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $priorityCls }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </dd>
                    </div>
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Assigned To</dt>
                        <dd class="font-medium text-on-surface text-xs">{{ $task->assignedTo?->name ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Created By</dt>
                        <dd class="font-medium text-on-surface text-xs">{{ $task->createdBy?->name ?? '—' }}</dd>
                    </div>
                    @if ($task->due_date)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Due Date</dt>
                        <dd class="text-xs font-semibold {{ $task->isOverdue() ? 'text-status-blocked' : ($task->isDueSoon() ? 'text-status-payment-due' : 'text-on-surface') }}">
                            {{ $task->due_date->format('d M Y') }}
                            @if ($task->isOverdue()) <span class="font-normal">(overdue)</span>
                            @elseif ($task->isDueSoon()) <span class="font-normal">(due soon)</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                    @if ($task->started_at)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Started</dt>
                        <dd class="text-xs text-on-surface-variant">{{ $task->started_at->format('d M Y H:i') }}</dd>
                    </div>
                    @endif
                    @if ($task->submitted_at)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Submitted</dt>
                        <dd class="text-xs text-on-surface-variant">{{ $task->submitted_at->format('d M Y H:i') }}</dd>
                    </div>
                    @endif
                    @if ($task->approved_at)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Approved</dt>
                        <dd class="text-xs text-status-active font-semibold">{{ $task->approved_at->format('d M Y H:i') }}</dd>
                    </div>
                    @endif
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Created</dt>
                        <dd class="text-xs text-on-surface-variant">{{ $task->created_at->format('d M Y') }}</dd>
                    </div>
                    <div class="px-5 py-3 flex items-center justify-between">
                        <dt class="text-xs text-outline">Workspace</dt>
                        <dd>
                            <a href="{{ route('workspace.show', $workspace) }}" class="text-xs text-secondary hover:brightness-110 transition-all font-mono">
                                {{ $workspace->workspace_code }}
                            </a>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- ── Attachments ──────────────────────────────────────────────── --}}
            @php
                $taskFiles = $task->files()
                    ->with('uploadedBy')
                    ->when(! $isAdminOrManager, fn ($q) => $q->where('visibility', 'public'))
                    ->get();

                $canUploadFile = ! in_array($role, ['observer', 'none'], true);
                $currentUserIdForFiles = (int) auth()->id();
            @endphp
            <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 border-b border-border-subtle flex items-center justify-between"
                     style="background:rgba(247,249,251,1);">
                    <h3 class="text-xs font-semibold text-outline uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-secondary" style="font-size: 14px;">attach_file</span>
                        Attachments
                    </h3>
                    @if ($taskFiles->count() > 0)
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                              style="background:rgba(0,88,190,0.08);color:#0058be;">
                            {{ $taskFiles->count() }}
                        </span>
                    @endif
                </div>

                <div class="p-4 space-y-2">
                    @forelse ($taskFiles as $tf)
                    @php
                        $tfCanDelete = $isAdminOrManager || (int) $tf->uploaded_by_user_id === $currentUserIdForFiles;
                    @endphp
                    <div class="flex items-center justify-between gap-2 p-2.5 rounded-lg"
                         style="background:#F8FAFC;border:1px solid #F1F5F9;">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="material-symbols-outlined flex-shrink-0" style="font-size:14px;color:#0058be;">{{ $tf->typeIcon() }}</span>
                            <div class="min-w-0">
                                <p class="text-xs font-medium truncate text-on-surface">
                                    {{ $tf->title ?: $tf->original_filename }}
                                </p>
                                <p class="text-[10px] text-outline">
                                    {{ $tf->formattedSize() }} · {{ $tf->created_at->format('d M') }}
                                    @if ($tf->isInternal())
                                        · <span style="color:#0058be;">Internal</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <a href="{{ route('workspace.files.download', [$workspace, $tf]) }}"
                               class="inline-flex items-center px-2 py-1 rounded text-[10px] font-semibold text-white"
                               style="background:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 11px;">download</span>
                            </a>
                            @if ($tfCanDelete)
                                <form method="POST"
                                      action="{{ route('workspace.files.destroy', [$workspace, $tf]) }}"
                                      onsubmit="return confirm('Remove this attachment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center px-2 py-1 rounded text-[10px]"
                                            style="color:#DC2626;background:#FFF5F5;border:1px solid #FECACA;">
                                        <span class="material-symbols-outlined" style="font-size: 11px;">delete</span>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @empty
                        <p class="text-xs italic text-outline">No attachments yet.</p>
                    @endforelse

                    @if ($canUploadFile)
                    <form method="POST"
                          action="{{ route('workspace.tasks.files.store', [$workspace, $task]) }}"
                          enctype="multipart/form-data"
                          class="pt-2 space-y-2">
                        @csrf
                        <input type="hidden" name="category" value="task_attachment">
                        <div class="flex items-center gap-2">
                            <input type="file"
                                   name="file"
                                   required
                                   accept="{{ implode(',', array_map(fn($m) => '.' . $m, \App\Models\WorkspaceFile::allowedMimes())) }}"
                                   class="flex-1 text-xs rounded border px-2 py-1.5 min-w-0"
                                   style="border-color:#CBD5E1;color:#374151;background:#fff;">
                            <button type="submit"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-semibold text-white flex-shrink-0"
                                    style="background:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 12px;">upload</span>
                                Attach
                            </button>
                        </div>
                        @if ($isAdminOrManager)
                            <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                <input type="checkbox" name="visibility" value="internal" class="rounded border-border-subtle">
                                <span class="text-[10px] text-outline">Internal attachment</span>
                            </label>
                        @else
                            <input type="hidden" name="visibility" value="public">
                        @endif
                        <p class="text-[10px] text-outline">Max 10 MB</p>
                    </form>
                    @endif
                </div>
            </div>

            {{-- ── Time Logs for this task ──────────────────────────────────── --}}
            @php
                $taskTimeLogs       = $task->timeLogs()->with('user')->orderByDesc('log_date')->limit(5)->get();
                $taskTimeLogRole    = $workspace->resolveUserWorkspaceRole(auth()->user());
                $canLogTimeForTask  = \App\Models\WorkspaceTimeLog::canCreate($taskTimeLogRole);
                $canSeeTimeLogs     = $taskTimeLogRole !== 'observer'
                                      && !in_array($taskTimeLogRole, ['client_admin','client_staff','client'], true);
                $activeTaskTimer    = \App\Models\WorkspaceTimeLog::activeTimerFor(auth()->user());
                $activeTimerForThisTask = $activeTaskTimer
                    && (int) $activeTaskTimer->workspace_id === (int) $workspace->id
                    && (int) $activeTaskTimer->workspace_task_id === (int) $task->id;
                $canStartTimerForTask = $canLogTimeForTask
                    && ! $activeTaskTimer
                    && in_array($task->status, ['pending', 'in_progress', 'blocked', 'revision_requested'], true);
                $taskRunningTimers = $isAdminOrManager
                    ? $task->timeLogs()->with('user')->running()->orderByDesc('started_at')->get()
                    : collect();
            @endphp
            @if ($canSeeTimeLogs)
                <div class="bg-white rounded-xl border border-border-subtle shadow-sm overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-border-subtle flex items-center justify-between"
                         style="background:rgba(247,249,251,1);">
                        <h3 class="text-xs font-semibold text-outline uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-secondary" style="font-size: 14px;">schedule</span>
                            Time Logs
                        </h3>
                        @if ($canLogTimeForTask)
                            <a href="{{ route('workspace.time-logs.create', $workspace) }}?task={{ $task->id }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-semibold text-white"
                               style="background:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 12px;">add</span>
                                Log Time
                            </a>
                        @endif
                    </div>

                    <div class="p-4 space-y-3">
                        @if ($activeTimerForThisTask)
                            <div class="p-3 rounded-lg"
                                 style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.24);">
                                <p class="text-[10px] font-bold uppercase tracking-wide mb-1" style="color:#059669;">Timer running</p>
                                <p class="font-mono text-base font-bold text-on-surface js-running-timer"
                                   data-started-at="{{ $activeTaskTimer->started_at?->toIso8601String() }}">
                                    {{ $activeTaskTimer->durationForHumans() }}
                                </p>
                                <div class="mt-3 space-y-2">
                                    <form method="POST" action="{{ route('workspace.time-tracker.stop', $workspace) }}">
                                        @csrf
                                        <input type="hidden" name="time_log_id" value="{{ $activeTaskTimer->id }}">
                                        <input type="hidden" name="status" value="draft">
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border"
                                                style="border-color:#F59E0B;color:#92400E;background:rgba(245,158,11,0.08);">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">stop_circle</span>
                                            Clock Out
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('workspace.time-tracker.complete', $workspace) }}" class="space-y-2">
                                        @csrf
                                        <input type="hidden" name="time_log_id" value="{{ $activeTaskTimer->id }}">
                                        <input type="text" name="work_summary" required maxlength="1000"
                                               placeholder="Work summary"
                                               class="w-full px-3 py-2 rounded-lg border border-border-subtle text-xs focus:outline-none focus:ring-2 focus:ring-secondary/20">
                                        <button type="submit"
                                                class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white"
                                                style="background:#0058be;">
                                            <span class="material-symbols-outlined" style="font-size: 14px;">task_alt</span>
                                            Complete Session
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @elseif ($activeTaskTimer)
                            <div class="p-3 rounded-lg text-xs text-on-surface-variant"
                                 style="background:#F8FAFC;border:1px solid #E2E8F0;">
                                You already have a running timer.
                                @if ($activeTaskTimer->workspace)
                                    <a href="{{ route('workspace.time-logs.show', [$activeTaskTimer->workspace, $activeTaskTimer]) }}"
                                       class="font-semibold underline" style="color:#0058be;">View active timer</a>
                                @endif
                            </div>
                        @elseif ($canStartTimerForTask)
                            <form method="POST" action="{{ route('workspace.time-tracker.start', $workspace) }}">
                                @csrf
                                <input type="hidden" name="workspace_task_id" value="{{ $task->id }}">
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold text-white"
                                        style="background:#0058be;">
                                    <span class="material-symbols-outlined" style="font-size: 14px;">timer</span>
                                    Start Timer for This Task
                                </button>
                            </form>
                        @endif

                        @if ($isAdminOrManager && $taskRunningTimers->isNotEmpty())
                            <div class="space-y-2">
                                @foreach ($taskRunningTimers as $taskRunningTimer)
                                    <div class="flex items-center justify-between gap-2 text-xs p-2 rounded-lg"
                                         style="background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.18);">
                                        <span class="truncate text-on-surface">{{ $taskRunningTimer->user->name ?? 'Unknown user' }}</span>
                                        <span class="font-mono font-bold js-running-timer"
                                              data-started-at="{{ $taskRunningTimer->started_at?->toIso8601String() }}">
                                            {{ $taskRunningTimer->durationForHumans() }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($taskTimeLogs->isEmpty())
                            <p class="text-xs italic text-outline">No time logs for this task yet.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($taskTimeLogs as $tl)
                                    <div class="flex items-center justify-between text-xs">
                                        <div class="min-w-0">
                                            <span class="font-medium text-on-surface">{{ $tl->log_date ? $tl->log_date->format('d M') : '—' }}</span>
                                            <span class="text-outline ml-1">{{ $tl->user->name ?? '—' }}</span>
                                            <span class="text-on-surface-variant ml-1 truncate">— {{ Str::limit($tl->work_summary, 40) }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                            <span class="font-semibold" style="color:#0058be;">{{ $tl->durationForHumans() }}</span>
                                            <a href="{{ route('workspace.time-logs.show', [$workspace, $tl]) }}"
                                               class="text-[10px] font-semibold hover:underline" style="color:#0058be;">View</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="pt-2 border-t border-border-subtle">
                                <a href="{{ route('workspace.time-logs.index', $workspace) }}"
                                   class="text-[10px] font-semibold hover:underline" style="color:#0058be;">
                                    View all time logs →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Quick links --}}
            <div class="flex flex-col gap-2">
                <a href="{{ route('workspace.tasks.index', $workspace) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-secondary hover:brightness-110 transition-all border border-secondary/20"
                   style="background:rgba(0,88,190,0.03);">
                    <span class="material-symbols-outlined" style="font-size: 16px;">view_kanban</span>
                    Back to Task Board
                </a>
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-on-surface-variant hover:text-secondary transition-all border border-border-subtle">
                    <span class="material-symbols-outlined" style="font-size: 16px;">workspaces</span>
                    Back to Workspace
                </a>
            </div>

        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function formatElapsed(startedAt) {
            var started = new Date(startedAt);
            var totalSeconds = Math.max(0, Math.floor((Date.now() - started.getTime()) / 1000));
            var hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
            var minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
            var seconds = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
            return hours + ':' + minutes + ':' + seconds;
        }

        document.querySelectorAll('.js-running-timer[data-started-at]').forEach(function (timer) {
            var tick = function () {
                timer.textContent = formatElapsed(timer.dataset.startedAt);
            };
            tick();
            window.setInterval(tick, 1000);
        });
    });
</script>

</x-layouts.gvos>
