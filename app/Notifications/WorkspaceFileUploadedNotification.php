<?php

namespace App\Notifications;

use App\Models\WorkspaceFile;

class WorkspaceFileUploadedNotification extends GvosNotification
{
    public function __construct(WorkspaceFile $file)
    {
        $displayName = $file->title ?: $file->original_filename ?: 'A workspace file';

        parent::__construct('file_uploaded', [
            'title' => 'File uploaded',
            'message' => $displayName . ' was uploaded to a workspace you can access.',
            'action_url' => $file->workspace_task_id && $file->task
                ? route('workspace.tasks.show', [$file->workspace_id, $file->workspace_task_id])
                : route('workspace.files.index', $file->workspace_id),
            'workspace_id' => $file->workspace_id,
            'related_type' => WorkspaceFile::class,
            'related_id' => $file->id,
            'level' => 'info',
        ]);
    }
}
