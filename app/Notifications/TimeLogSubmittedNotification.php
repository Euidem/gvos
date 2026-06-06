<?php

namespace App\Notifications;

use App\Models\WorkspaceTimeLog;

class TimeLogSubmittedNotification extends GvosNotification
{
    public function __construct(WorkspaceTimeLog $timeLog)
    {
        parent::__construct('time_log_submitted', [
            'title' => 'Time log submitted',
            'message' => 'A work session was submitted for manager review.',
            'action_url' => route('workspace.time-logs.show', [$timeLog->workspace_id, $timeLog->id]),
            'workspace_id' => $timeLog->workspace_id,
            'related_type' => WorkspaceTimeLog::class,
            'related_id' => $timeLog->id,
            'level' => 'info',
        ]);
    }
}
