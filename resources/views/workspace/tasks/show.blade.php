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
@endphp

    {{-- ── Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-6">
        <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <a href="{{ route('workspace.tasks.index', $workspace) }}" class="hover:text-secondary transition-colors">Task Board</a>
        <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
        <span class="font-mono">{{ $task->task_code }}</span>
    </div>

    {{-- ── Session messages --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-status-active/10 border border-status-active/20 rounded-lg text-sm text-status-active">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 p-4 bg-status-blocked/10 border border-status-blocked/20 rounded-lg">
            @foreach ($errors->all() as $error)
                <p class="text-xs text-status-blocked">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl">

        {{-- ── Main column ──────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Task header card --}}
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm overflow-hidden">
                <div class="h-1 w-full" style="background-color:#0058be"></div>
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-[11px] font-mono text-outline">{{ $task->task_code }}</span>
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusCls }}">
                                    {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                                </span>
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $priorityCls }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </div>
                            <h2 class="text-xl font-bold text-[#191c1e] leading-tight">{{ $task->title }}</h2>
                        </div>
                        @if ($canEdit)
                            <a href="{{ route('workspace.tasks.edit', [$workspace, $task]) }}"
                               class="flex-shrink-0 text-xs text-secondary hover:brightness-110 transition-all flex items-center gap-1">
                                <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                Edit
                            </a>
                        @endif
                    </div>

                    @if ($task->description)
                        <div class="prose prose-sm max-w-none text-[#45464d] text-sm leading-relaxed border-t border-[#E2E8F0] pt-4 mt-4 whitespace-pre-wrap">{{ $task->description }}</div>
                    @else
                        <p class="text-sm italic text-outline border-t border-[#E2E8F0] pt-4 mt-4">No description provided.</p>
                    @endif

                    {{-- Internal notes (admins/managers only) --}}
                    @if ($isAdminOrManager && $task->internal_notes)
                        <div class="mt-4 p-3 bg-secondary/5 border border-secondary/20 rounded-lg">
                            <p class="text-[11px] font-semibold text-secondary uppercase tracking-wider mb-1">Internal Notes</p>
                            <p class="text-xs text-[#45464d] whitespace-pre-wrap">{{ $task->internal_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Status action form --}}
            @if (! empty($allowedTransitions))
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
                <h3 class="text-sm font-bold text-[#191c1e] mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">swap_horiz</span>
                    Change Status
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

            {{-- ── Comments --}}
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-6">
                <h3 class="text-sm font-bold text-[#191c1e] mb-5 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 18px;">chat_bubble</span>
                    Comments
                    <span class="text-xs font-normal text-outline ml-1">({{ $comments->count() }})</span>
                </h3>

                @forelse ($comments as $comment)
                <div class="mb-4 last:mb-0 {{ $comment->isInternal() ? 'pl-3 border-l-2 border-secondary/30' : '' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0 mt-0.5"
                             style="{{ $comment->isInternal() ? 'background-color:#0058be' : 'background-color:#2170e4' }}">
                            {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-semibold text-[#191c1e]">{{ $comment->user->name ?? 'Unknown' }}</span>
                                @if ($comment->isInternal())
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-secondary/10 text-secondary">Internal</span>
                                @endif
                                <span class="text-[11px] text-outline">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-[#45464d] whitespace-pre-wrap leading-relaxed">{{ $comment->comment }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-sm italic text-outline text-center py-4">No comments yet. Be the first to add one.</p>
                @endforelse

                {{-- Add comment form --}}
                <div class="mt-6 border-t border-[#E2E8F0] pt-5">
                    <form method="POST" action="{{ route('workspace.tasks.comments.store', [$workspace, $task]) }}" class="space-y-3">
                        @csrf
                        <textarea name="comment" rows="3"
                                  placeholder="Add a comment..."
                                  required maxlength="5000"
                                  class="w-full px-4 py-3 border border-[#E2E8F0] rounded-lg text-sm text-[#191c1e] focus:outline-none focus:ring-2 focus:ring-[#0058be]/20 focus:border-[#0058be] resize-y @error('comment') border-status-blocked @enderror">{{ old('comment') }}</textarea>
                        @error('comment')
                            <p class="text-xs text-status-blocked">{{ $message }}</p>
                        @enderror

                        <div class="flex items-center gap-3">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all flex items-center gap-2"
                                    style="background-color:#0058be">
                                <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                                Post Comment
                            </button>

                            @if ($isAdminOrManager)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="visibility" value="internal"
                                           class="h-4 w-4 rounded border-[#E2E8F0] text-secondary"
                                           {{ old('visibility') === 'internal' ? 'checked' : '' }}>
                                    <span class="text-xs text-[#45464d]">Internal (hidden from clients and talent)</span>
                                </label>
                            @else
                                <input type="hidden" name="visibility" value="public">
                            @endif
                        </div>
                    </form>
                </div>
            </div>

        </div>

        {{-- ── Sidebar column ────────────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Task meta --}}
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
                <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider mb-4">Task Details</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Status</dt>
                        <dd>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $statusCls }}">
                                {{ $statusLabels[$task->status] ?? ucfirst($task->status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Priority</dt>
                        <dd>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $priorityCls }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Assigned To</dt>
                        <dd class="font-medium text-[#191c1e]">
                            {{ $task->assignedTo?->name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Created By</dt>
                        <dd class="font-medium text-[#191c1e]">{{ $task->createdBy?->name ?? '—' }}</dd>
                    </div>
                    @if ($task->due_date)
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Due Date</dt>
                        <dd class="font-medium {{ $task->isOverdue() ? 'text-status-blocked' : ($task->isDueSoon() ? 'text-status-payment-due' : 'text-[#191c1e]') }}">
                            {{ $task->due_date->format('d M Y') }}
                            @if ($task->isOverdue()) <span class="text-xs">(overdue)</span>
                            @elseif ($task->isDueSoon()) <span class="text-xs">(due soon)</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                    @if ($task->started_at)
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Started</dt>
                        <dd class="text-xs text-[#45464d]">{{ $task->started_at->format('d M Y H:i') }}</dd>
                    </div>
                    @endif
                    @if ($task->submitted_at)
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Submitted</dt>
                        <dd class="text-xs text-[#45464d]">{{ $task->submitted_at->format('d M Y H:i') }}</dd>
                    </div>
                    @endif
                    @if ($task->approved_at)
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Approved</dt>
                        <dd class="text-xs text-status-active">{{ $task->approved_at->format('d M Y H:i') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Created</dt>
                        <dd class="text-xs text-[#45464d]">{{ $task->created_at->format('d M Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-outline mb-0.5">Workspace</dt>
                        <dd>
                            <a href="{{ route('workspace.show', $workspace) }}" class="text-xs text-secondary hover:brightness-110 transition-all font-mono">
                                {{ $workspace->workspace_code }}
                            </a>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- ── Task Files (Phase 6) ──────────────────────────────────── --}}
            @php
                $taskFiles = $task->files()
                    ->with('uploadedBy')
                    ->when(! $isAdminOrManager, fn ($q) => $q->where('visibility', 'public'))
                    ->get();

                $canUploadFile = ! in_array($role, ['observer', 'none'], true);
                $currentUserIdForFiles = (int) auth()->id();
            @endphp
            <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-5">
                <h3 class="text-xs font-bold text-on-surface uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary" style="font-size: 15px;">attach_file</span>
                    Attachments
                    @if ($taskFiles->count() > 0)
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                              style="background:rgba(0,88,190,.08);color:#0058be;">
                            {{ $taskFiles->count() }}
                        </span>
                    @endif
                </h3>

                {{-- Existing attachments --}}
                @forelse ($taskFiles as $tf)
                @php
                    $tfCanDelete = $isAdminOrManager || (int) $tf->uploaded_by_user_id === $currentUserIdForFiles;
                @endphp
                <div class="flex items-center justify-between gap-2 mb-2 p-2 rounded-lg"
                     style="background:#F8FAFC; border:1px solid #F1F5F9;">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="material-symbols-outlined flex-shrink-0" style="font-size:15px; color:#0058be;">{{ $tf->typeIcon() }}</span>
                        <div class="min-w-0">
                            <p class="text-xs font-medium truncate" style="color:#1E293B;">
                                {{ $tf->title ?: $tf->original_filename }}
                            </p>
                            <p class="text-[10px]" style="color:#9CA3AF;">
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
                           style="background-color:#0058be;">
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
                    <p class="text-xs italic text-outline mb-3">No attachments yet.</p>
                @endforelse

                {{-- Upload attachment form --}}
                @if ($canUploadFile)
                <form method="POST"
                      action="{{ route('workspace.tasks.files.store', [$workspace, $task]) }}"
                      enctype="multipart/form-data"
                      class="border-t border-[#F1F5F9] pt-3 mt-3 space-y-2">
                    @csrf
                    <input type="hidden" name="category" value="task_attachment">
                    <div class="flex items-center gap-2">
                        <input type="file"
                               name="file"
                               required
                               accept="{{ implode(',', array_map(fn($m) => '.' . $m, \App\Models\WorkspaceFile::allowedMimes())) }}"
                               class="flex-1 text-xs rounded border px-2 py-1.5 min-w-0"
                               style="border-color:#CBD5E1; color:#374151; background:#fff;">
                        <button type="submit"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded text-xs font-semibold text-white flex-shrink-0"
                                style="background-color:#0058be;">
                            <span class="material-symbols-outlined" style="font-size: 12px;">upload</span>
                            Attach
                        </button>
                    </div>
                    @if ($isAdminOrManager)
                        <label class="flex items-center gap-1.5 cursor-pointer select-none">
                            <input type="checkbox" name="visibility" value="internal" class="rounded border-border-subtle">
                            <span class="text-[10px]" style="color:#64748B;">Internal attachment</span>
                        </label>
                    @else
                        <input type="hidden" name="visibility" value="public">
                    @endif
                    <p class="text-[10px]" style="color:#9CA3AF;">Max 10 MB</p>
                </form>
                @endif
            </div>

            {{-- ── Time Logs for this task (Phase 7) ──────────────────────── --}}
            @php
                $taskTimeLogs       = $task->timeLogs()->with('user')->orderByDesc('log_date')->limit(5)->get();
                $taskTimeLogRole    = $workspace->resolveUserWorkspaceRole(auth()->user());
                $canLogTimeForTask  = \App\Models\WorkspaceTimeLog::canCreate($taskTimeLogRole);
                $canSeeTimeLogs     = $taskTimeLogRole !== 'observer'
                                      && !in_array($taskTimeLogRole, ['client_admin','client_staff','client'], true);
            @endphp
            @if ($canSeeTimeLogs)
                <div class="bg-white rounded-xl border border-[#E2E8F0] shadow-sm p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-outline uppercase tracking-wide flex items-center gap-1.5">
                            <span class="material-symbols-outlined" style="font-size: 14px; color:#0058be;">schedule</span>
                            Time Logs
                        </h3>
                        @if ($canLogTimeForTask)
                            <a href="{{ route('workspace.time-logs.create', $workspace) }}?task={{ $task->id }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-semibold text-white"
                               style="background-color:#0058be;">
                                <span class="material-symbols-outlined" style="font-size: 12px;">add</span>
                                Log Time
                            </a>
                        @endif
                    </div>

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
                        <div class="mt-3 pt-2 border-t border-[#F1F5F9]">
                            <a href="{{ route('workspace.time-logs.index', $workspace) }}"
                               class="text-[10px] font-semibold hover:underline" style="color:#0058be;">
                                View all time logs →
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Back links --}}
            <div class="space-y-2">
                <a href="{{ route('workspace.tasks.index', $workspace) }}"
                   class="flex items-center gap-2 text-sm text-secondary hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                    Back to Task Board
                </a>
                <a href="{{ route('workspace.show', $workspace) }}"
                   class="flex items-center gap-2 text-sm text-secondary hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined" style="font-size: 16px;">workspaces</span>
                    Back to Workspace
                </a>
            </div>

        </div>
    </div>

</x-layouts.gvos>
