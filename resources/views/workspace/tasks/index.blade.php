<x-layouts.gvos :title="$workspace->name . ' — Kanban Board'">

@php
    $statusCols = [
        'pending'            => ['label' => 'Pending',            'icon' => 'pending_actions', 'color' => '#D97706', 'headerBg' => '#FFFBEB', 'headerBorder' => '#FDE68A'],
        'in_progress'        => ['label' => 'In Progress',        'icon' => 'play_circle',     'color' => '#0058be', 'headerBg' => '#EFF6FF', 'headerBorder' => '#BFDBFE'],
        'blocked'            => ['label' => 'Blocked',            'icon' => 'block',           'color' => '#DC2626', 'headerBg' => '#FFF5F5', 'headerBorder' => '#FECACA'],
        'submitted'          => ['label' => 'Submitted',          'icon' => 'upload',          'color' => '#7C3AED', 'headerBg' => '#F5F3FF', 'headerBorder' => '#DDD6FE'],
        'revision_requested' => ['label' => 'Revision Req.',      'icon' => 'edit_note',       'color' => '#EA580C', 'headerBg' => '#FFF7ED', 'headerBorder' => '#FED7AA'],
        'approved'           => ['label' => 'Approved',           'icon' => 'check_circle',    'color' => '#059669', 'headerBg' => '#F0FDF4', 'headerBorder' => '#A7F3D0'],
        'closed'             => ['label' => 'Closed',             'icon' => 'task_alt',        'color' => '#6B7280', 'headerBg' => '#F9FAFB', 'headerBorder' => '#E5E7EB'],
    ];
    $priorityBadge = [
        'low'    => ['bg' => '#F1F5F9', 'color' => '#64748B', 'label' => 'Low'],
        'normal' => ['bg' => '#EFF6FF', 'color' => '#0058be', 'label' => 'Normal'],
        'high'   => ['bg' => '#FFFBEB', 'color' => '#D97706', 'label' => 'High'],
        'urgent' => ['bg' => '#FEF2F2', 'color' => '#DC2626', 'label' => 'URGENT'],
    ];
    // Roles that can drag at all (enforces SortableJS init).
    // 'assigned_user' = user assigned to a task but with no explicit member row;
    // they get talent-level drag rights (server enforces the per-task restriction).
    // Per-card drag handle visibility adds a second layer per role.
    // 'client_admin' / 'client' can drag only on submitted/approved columns (handled below).
    $draggableRoles = ['admin', 'workspace_admin', 'manager', 'talent', 'assigned_user', 'client_admin', 'client'];
@endphp

    {{-- ── Kanban-specific styles ─────────────────────────────────────────── --}}
    <style>
        /* Ghost placeholder while dragging */
        .sortable-ghost {
            opacity: 0.30 !important;
            border: 2px dashed #0058be !important;
            border-radius: 12px !important;
            background: rgba(0,88,190,0.04) !important;
            box-shadow: none !important;
        }
        /* The card being dragged (clone) */
        .sortable-chosen {
            box-shadow: 0 16px 40px rgba(0,0,0,0.20) !important;
            transform: rotate(1.5deg) scale(1.02) !important;
            z-index: 9999 !important;
            transition: none !important;
        }
        /* Valid drop target column highlight */
        .kanban-col-drop {
            background: rgba(0,88,190,0.04) !important;
            outline: 2px dashed rgba(0,88,190,0.35);
            outline-offset: -3px;
            border-radius: 12px;
        }
        /* Drag handle */
        .drag-handle {
            cursor: grab;
            color: #CBD5E1;
            transition: color 0.15s;
            user-select: none;
            flex-shrink: 0;
        }
        .drag-handle:hover  { color: #94A3B8; }
        .drag-handle:active { cursor: grabbing; }
        /* Card */
        .kanban-card {
            cursor: pointer;
            transition: box-shadow 0.15s ease, border-color 0.15s ease;
            user-select: none;
        }
        .kanban-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.10);
            border-color: rgba(0,88,190,0.25) !important;
        }
        /* Droppable list minimum height so empty cols are still drop targets */
        .kanban-task-list { min-height: 72px; }
        /* Toast system */
        #kanban-toast {
            position: fixed;
            top: 20px;
            right: 24px;
            z-index: 9999;
            pointer-events: none;
        }
        .toast-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.14);
            opacity: 0;
            transform: translateX(24px);
            transition: opacity 0.2s ease, transform 0.2s ease;
            pointer-events: none;
            max-width: 380px;
        }
        .toast-item.show { opacity: 1; transform: translateX(0); }
        .toast-success { background:#ECFDF5; color:#065F46; border:1px solid rgba(16,185,129,0.30); }
        .toast-error   { background:#FEF2F2; color:#991B1B; border:1px solid rgba(220,38,38,0.30); }
    </style>

    {{-- ── Toast mount ────────────────────────────────────────────────────── --}}
    <div id="kanban-toast"></div>

    {{-- ── Page header ──────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <div class="flex items-center gap-2 text-sm text-on-surface-variant mb-1">
                <a href="{{ route('workspace.show', $workspace) }}" class="hover:text-secondary transition-colors">{{ $workspace->name }}</a>
                <span class="material-symbols-outlined" style="font-size: 14px;">chevron_right</span>
                <span>Kanban Board</span>
            </div>
            <h2 class="text-xl font-bold text-on-surface">Kanban Board</h2>
            <p class="text-xs text-outline mt-0.5">
                <span id="kanban-total-count">{{ $tasks->count() }}</span>
                task{{ $tasks->count() !== 1 ? 's' : '' }}
                &middot; {{ $workspace->workspace_code }}
                @if (in_array($role, $draggableRoles))
                    &middot; <span style="color:#0058be;">Drag cards between columns to change status</span>
                @endif
            </p>
            {{-- PART I: debug role line — visible only for admin / workspace_admin / manager --}}
            @if (!empty($showDebugRole))
                <p class="text-xs mt-1" style="color:#9CA3AF;">
                    Task role: <span style="font-weight:600; color:#6B7280;">{{ $effectiveRole ?? $role }}</span>
                </p>
            @endif
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

    {{-- ── Session flash ──────────────────────────────────────────────────── --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.25);color:#065F46;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg text-sm"
             style="background:rgba(220,38,38,0.08);border:1px solid rgba(220,38,38,0.25);color:#991B1B;">
            <span class="material-symbols-outlined flex-shrink-0" style="font-size: 18px;">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Kanban board — horizontal scrollable ─────────────────────────── --}}
    <div class="overflow-x-auto pb-6" style="margin: 0 -4px; padding: 0 4px;">
        <div class="flex gap-4" style="min-width: max-content;">

            @foreach ($statusCols as $statusKey => $col)
            @php $colTasks = $tasksByStatus->get($statusKey, collect()); @endphp

            <div class="flex flex-col w-72 flex-shrink-0" data-column-status="{{ $statusKey }}">

                {{-- ── Column header ──────────────────────────────────────── --}}
                <div class="flex items-center justify-between mb-2.5 px-3 py-2.5 rounded-xl"
                     style="background-color:{{ $col['headerBg'] }}; border:1px solid {{ $col['headerBorder'] }};">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined" style="font-size:15px; color:{{ $col['color'] }};">{{ $col['icon'] }}</span>
                        <span class="text-xs font-bold uppercase tracking-wide" style="color:{{ $col['color'] }};">{{ $col['label'] }}</span>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full text-white"
                          id="col-count-{{ $statusKey }}"
                          style="background-color:{{ $col['color'] }}; min-width:22px; text-align:center;">
                        {{ $colTasks->count() }}
                    </span>
                </div>

                {{-- ── Droppable task list ─────────────────────────────────── --}}
                <div class="kanban-task-list flex-1 space-y-3 rounded-xl p-1.5 transition-all"
                     data-status="{{ $statusKey }}">

                    @foreach ($colTasks as $task)
                    @php
                        $badge = $priorityBadge[$task->priority] ?? ['bg' => '#F1F5F9', 'color' => '#64748B', 'label' => ucfirst($task->priority)];
                        $dateColor = $task->isOverdue() ? '#DC2626' : ($task->isDueSoon() ? '#D97706' : '#9CA3AF');

                        // Drag handle visibility rules:
                        //   admin/workspace_admin/manager: always show (full board control)
                        //   talent:         show on tasks assigned to this user OR unassigned
                        //   assigned_user:  show only on their explicitly assigned task
                        //   client_admin/client: show only where they can act (submitted/approved)
                        //   client_staff/observer/others: never show
                        $taskAssigneeId = (int) ($task->assigned_to_user_id ?? 0);
                        $showDragHandle = match ($role) {
                            'admin', 'workspace_admin', 'manager'
                                            => true,
                            'talent'        => $taskAssigneeId === 0 || $taskAssigneeId === $currentUserId,
                            'assigned_user' => $taskAssigneeId === $currentUserId,
                            'client_admin', 'client'
                                            => in_array($task->status, ['submitted', 'approved']),
                            default         => false,
                        };
                    @endphp
                    <div class="kanban-card bg-white rounded-xl p-4"
                         style="border:1px solid #E2E8F0; box-shadow:0 1px 3px rgba(0,0,0,0.06);"
                         data-task-id="{{ $task->id }}"
                         data-current-status="{{ $task->status }}"
                         data-href="{{ route('workspace.tasks.show', [$workspace, $task]) }}">

                        {{-- Row 1: priority badge · code · drag handle --}}
                        <div class="flex items-center justify-between gap-2 mb-2.5">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                  style="background-color:{{ $badge['bg'] }}; color:{{ $badge['color'] }};">
                                {{ $badge['label'] }}
                            </span>
                            <div class="flex items-center gap-1">
                                <span class="text-[10px] font-mono" style="color:#9CA3AF;">{{ $task->task_code }}</span>
                                @if ($showDragHandle)
                                    <span class="drag-handle material-symbols-outlined" style="font-size:18px;" title="Drag to change status">drag_indicator</span>
                                @endif
                            </div>
                        </div>

                        {{-- Row 2: title --}}
                        <p class="text-sm font-semibold leading-snug mb-3" style="color:#1E293B;">
                            {{ Str::limit($task->title, 80) }}
                        </p>

                        {{-- Row 3: assignee · comments · due date --}}
                        <div class="flex items-center justify-between" style="font-size:11px; color:#94A3B8;">
                            {{-- Assignee --}}
                            <div class="flex items-center gap-1.5 min-w-0">
                                @if ($task->assignedTo)
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center text-[9px] font-bold text-white flex-shrink-0"
                                         style="background-color:#0058be;">
                                        {{ strtoupper(substr($task->assignedTo->name, 0, 1)) }}
                                    </div>
                                    <span class="truncate">{{ Str::limit($task->assignedTo->name, 16) }}</span>
                                @else
                                    <span class="italic">Unassigned</span>
                                @endif
                            </div>
                            {{-- Comments + due date --}}
                            <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                @if ($task->comments_count > 0)
                                    <span class="flex items-center gap-0.5">
                                        <span class="material-symbols-outlined" style="font-size:13px;">chat_bubble</span>
                                        {{ $task->comments_count }}
                                    </span>
                                @endif
                                @if ($task->due_date)
                                    <span class="flex items-center gap-0.5 font-semibold" style="color:{{ $dateColor }};">
                                        <span class="material-symbols-outlined" style="font-size:13px;">calendar_today</span>
                                        {{ $task->due_date->format('d M') }}
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                    @endforeach

                </div>

                {{-- ── Column empty state (outside sortable) ──────────────── --}}
                <div class="kanban-empty-msg text-center py-3 mt-1 {{ $colTasks->isEmpty() ? '' : 'hidden' }}">
                    <p class="text-xs italic" style="color:#CBD5E1;">No tasks here</p>
                </div>

            </div>
            @endforeach

        </div>
    </div>

    {{-- ── Global empty state (no tasks at all) ───────────────────────────── --}}
    @if ($tasks->isEmpty())
        @php $__taskUser = auth()->user(); @endphp
        <div class="bg-white rounded-xl shadow-sm p-12 text-center"
             style="border:1px solid #E2E8F0; margin-top: -8px;">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center mx-auto mb-4"
                 style="background-color:rgba(0,88,190,.06);">
                <span class="material-symbols-outlined" style="font-size:28px; color:#0058be;">view_kanban</span>
            </div>
            <h4 class="text-sm font-semibold mb-2" style="color:#1E293B;">No tasks yet</h4>
            @if ($canCreate)
                <p class="text-xs max-w-xs mx-auto mb-4" style="color:#94A3B8;">
                    Tasks help track work inside this workspace. Create the first task to get started.
                </p>
                <a href="{{ route('workspace.tasks.create', $workspace) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white"
                   style="background-color:#0058be">
                    <span class="material-symbols-outlined" style="font-size:16px;">add</span>
                    Create First Task
                </a>
            @elseif ($__taskUser->hasRole('talent'))
                <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                    Your manager has not assigned any tasks yet. Check back soon or reach out via workspace chat.
                </p>
            @elseif ($__taskUser->hasAnyRole(['individual_client','business_client_admin','business_client_staff']))
                <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                    Tasks will appear here once your manager sets up your project deliverables.
                </p>
            @else
                <p class="text-xs max-w-xs mx-auto" style="color:#94A3B8;">
                    No tasks have been created in this workspace yet.
                </p>
            @endif
        </div>
    @endif

    {{-- ── SortableJS ──────────────────────────────────────────────────────── --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
    (function () {
        'use strict';

        // ── Config ──────────────────────────────────────────────────────────
        var WORKSPACE_ID = {{ (int) $workspace->id }};
        var CAN_DRAG     = {{ in_array($role, $draggableRoles) ? 'true' : 'false' }};
        var CSRF         = '{{ csrf_token() }}';

        if (!CAN_DRAG || typeof Sortable === 'undefined') return;

        // ── Drag-state flag (prevents click-navigation on card drop) ────────
        var isDragging = false;

        // ── Toast helper ─────────────────────────────────────────────────────
        function showToast(message, type) {
            var container = document.getElementById('kanban-toast');
            if (!container) return;
            var t = document.createElement('div');
            t.className = 'toast-item toast-' + type;
            t.innerHTML =
                '<span style="font-family:\'Material Symbols Outlined\';font-size:16px;flex-shrink:0;">'
                + (type === 'success' ? 'check_circle' : 'error')
                + '</span><span>' + message + '</span>';
            container.appendChild(t);
            requestAnimationFrame(function () {
                requestAnimationFrame(function () { t.classList.add('show'); });
            });
            setTimeout(function () {
                t.classList.remove('show');
                setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 250);
            }, 4500);
        }

        // ── Column count badge updater ────────────────────────────────────────
        function updateColCount(status, delta) {
            var badge = document.getElementById('col-count-' + status);
            if (badge) {
                var n = parseInt(badge.textContent || '0', 10) + delta;
                badge.textContent = Math.max(0, n);
            }
        }

        // ── Column empty-state updater ────────────────────────────────────────
        function updateEmptyState(columnEl) {
            if (!columnEl) return;
            var list     = columnEl.querySelector('.kanban-task-list');
            var emptyMsg = columnEl.querySelector('.kanban-empty-msg');
            if (!list || !emptyMsg) return;
            var hasCards = list.querySelectorAll('.kanban-card').length > 0;
            emptyMsg.classList.toggle('hidden', hasCards);
        }

        // ── Revert a card to its original column ──────────────────────────────
        function revertCard(card, fromCol, toCol, oldIndex, oldStatus, newStatus) {
            if (card.parentNode === toCol) toCol.removeChild(card);
            var refEl = fromCol.children[oldIndex] || null;
            fromCol.insertBefore(card, refEl);
            updateColCount(newStatus, -1);
            updateColCount(oldStatus, +1);
            updateEmptyState(fromCol.closest('[data-column-status]'));
            updateEmptyState(toCol.closest('[data-column-status]'));
        }

        // ── Initialise SortableJS on each column ──────────────────────────────
        document.querySelectorAll('.kanban-task-list').forEach(function (listEl) {
            Sortable.create(listEl, {
                group:       'kanban-board',
                animation:   160,
                ghostClass:  'sortable-ghost',
                chosenClass: 'sortable-chosen',
                handle:      '.drag-handle',

                onStart: function () {
                    isDragging = true;
                },

                onEnd: function () {
                    setTimeout(function () { isDragging = false; }, 80);
                    // Clear all column highlights
                    document.querySelectorAll('.kanban-task-list').forEach(function (el) {
                        el.classList.remove('kanban-col-drop');
                    });
                },

                onMove: function (evt) {
                    // Highlight only the current drop target
                    document.querySelectorAll('.kanban-task-list').forEach(function (el) {
                        el.classList.remove('kanban-col-drop');
                    });
                    if (evt.to) evt.to.classList.add('kanban-col-drop');
                },

                onAdd: function (evt) {
                    // Card moved from one column to another
                    var card      = evt.item;
                    var newStatus = evt.to.dataset.status;
                    var oldStatus = card.dataset.currentStatus;
                    var taskId    = card.dataset.taskId;
                    var oldIndex  = evt.oldIndex; // position in source column before move

                    if (newStatus === oldStatus) return;

                    // Optimistic UI: update counts and empty-states immediately
                    updateColCount(oldStatus, -1);
                    updateColCount(newStatus, +1);
                    updateEmptyState(evt.from.closest('[data-column-status]'));
                    updateEmptyState(evt.to.closest('[data-column-status]'));

                    // AJAX status update — uses route-equivalent URL
                    var statusUrl = '/workspaces/' + WORKSPACE_ID + '/tasks/' + taskId + '/status';

                    fetch(statusUrl, {
                        method:  'POST',
                        headers: {
                            'Content-Type':     'application/json',
                            'Accept':           'application/json',
                            'X-CSRF-TOKEN':     CSRF,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ status: newStatus }),
                    })
                    .then(function (res) {
                        // Parse JSON regardless of HTTP status.
                        // If the body is not JSON (e.g., server-level HTML error page),
                        // the parse will throw and we fall to the .catch() handler.
                        return res.json().then(function (data) {
                            return { ok: res.ok, status: res.status, data: data };
                        });
                    })
                    .then(function (result) {
                        if (result.ok && result.data && result.data.success) {
                            // ── Move confirmed ──────────────────────────────
                            card.dataset.currentStatus = newStatus;
                            showToast(result.data.message || 'Task moved.', 'success');
                        } else {
                            // ── Move rejected — revert card ─────────────────
                            revertCard(card, evt.from, evt.to, oldIndex, oldStatus, newStatus);

                            // Log full context for debugging (PART G)
                            console.warn('[GVOS Kanban] Drag rejected', {
                                taskId:     taskId,
                                fromStatus: oldStatus,
                                toStatus:   newStatus,
                                httpStatus: result.status,
                                response:   result.data,
                            });

                            // Use the server's message when available, or a
                            // status-code-aware fallback if the message is missing.
                            var msg = (result.data && result.data.message)
                                ? result.data.message
                                : (result.status === 403
                                    ? 'You do not have permission to make this move.'
                                    : result.status === 422
                                    ? 'This status move is not allowed.'
                                    : result.status === 404
                                    ? 'Task or workspace not found. Please refresh.'
                                    : 'Could not move this task. Please try again.');

                            showToast(msg, 'error');
                        }
                    })
                    .catch(function (err) {
                        // Non-JSON response (server-level HTML error page, network
                        // failure, CORS block, etc.) — always revert the card.
                        revertCard(card, evt.from, evt.to, oldIndex, oldStatus, newStatus);

                        // Log for debugging (PART G)
                        console.warn('[GVOS Kanban] Drag network/parse error', {
                            taskId:     taskId,
                            fromStatus: oldStatus,
                            toStatus:   newStatus,
                            error:      err ? err.toString() : 'unknown',
                        });

                        showToast(
                            'The server returned an unexpected response. Please refresh the page and try again.',
                            'error'
                        );
                    });
                },
            });
        });

        // ── Card click → navigate to task detail ─────────────────────────────
        // Only fires for genuine clicks (not drags) thanks to isDragging flag.
        document.querySelectorAll('.kanban-card[data-href]').forEach(function (card) {
            card.addEventListener('click', function () {
                if (!isDragging) {
                    window.location.href = this.dataset.href;
                }
            });
        });

    })();
    </script>

</x-layouts.gvos>
