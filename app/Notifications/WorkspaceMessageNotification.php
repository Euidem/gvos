<?php

namespace App\Notifications;

use App\Models\WorkspaceMessage;

class WorkspaceMessageNotification extends GvosNotification
{
    public function __construct(WorkspaceMessage $message)
    {
        parent::__construct('workspace_message', [
            'title' => 'Workspace message',
            'message' => 'A ' . ($message->visibility === 'internal' ? 'restricted' : 'workspace')
                . ' message was posted.',
            'action_url' => route('workspace.chat.index', $message->workspace_id),
            'workspace_id' => $message->workspace_id,
            'related_type' => WorkspaceMessage::class,
            'related_id' => $message->id,
            'level' => 'info',
        ]);
    }
}
