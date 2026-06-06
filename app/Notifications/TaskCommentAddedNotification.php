<?php

namespace App\Notifications;

use App\Models\WorkspaceTaskComment;

class TaskCommentAddedNotification extends GvosNotification
{
    public function __construct(WorkspaceTaskComment $comment)
    {
        $task = $comment->task;

        parent::__construct('task_comment_added', [
            'title' => 'Task comment added',
            'message' => 'A ' . ($comment->visibility === 'internal' ? 'restricted' : 'public')
                . ' comment was added to task ' . ($task?->task_code ?? '#' . $comment->workspace_task_id) . '.',
            'action_url' => $task ? route('workspace.tasks.show', [$task->workspace_id, $task->id]) : route('workspace.index'),
            'workspace_id' => $task?->workspace_id,
            'related_type' => WorkspaceTaskComment::class,
            'related_id' => $comment->id,
            'level' => 'info',
        ]);
    }
}
