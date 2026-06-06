<?php

namespace App\Notifications;

use App\Models\WorkspaceTask;

class TaskAssignedNotification extends GvosNotification
{
    public function __construct(WorkspaceTask $task)
    {
        parent::__construct('task_assigned', [
            'title' => 'Task assigned',
            'message' => 'You have been assigned to task ' . ($task->task_code ?? '#' . $task->id) . ': ' . $task->title,
            'action_url' => route('workspace.tasks.show', [$task->workspace_id, $task->id]),
            'workspace_id' => $task->workspace_id,
            'related_type' => WorkspaceTask::class,
            'related_id' => $task->id,
            'level' => 'info',
        ]);
    }
}
