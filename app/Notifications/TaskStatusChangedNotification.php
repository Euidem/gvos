<?php

namespace App\Notifications;

use App\Models\WorkspaceTask;

class TaskStatusChangedNotification extends GvosNotification
{
    public function __construct(WorkspaceTask $task, string $oldStatus, string $newStatus)
    {
        $labels = WorkspaceTask::statusLabels();

        parent::__construct('task_status_changed', [
            'title' => 'Task status changed',
            'message' => 'Task ' . ($task->task_code ?? '#' . $task->id) . ' moved from '
                . ($labels[$oldStatus] ?? $oldStatus) . ' to ' . ($labels[$newStatus] ?? $newStatus) . '.',
            'action_url' => route('workspace.tasks.show', [$task->workspace_id, $task->id]),
            'workspace_id' => $task->workspace_id,
            'related_type' => WorkspaceTask::class,
            'related_id' => $task->id,
            'level' => $newStatus === 'blocked' ? 'warning' : 'info',
        ]);
    }
}
