<?php

namespace App\Notifications;

use App\Models\WorkspaceMember;

class WorkspaceMemberDeactivatedNotification extends GvosNotification
{
    public function __construct(WorkspaceMember $member)
    {
        $member->loadMissing('workspace');

        parent::__construct('workspace_member_deactivated', [
            'title' => 'Workspace access removed',
            'message' => 'Your access to workspace ' . ($member->workspace?->name ?? 'GVOS workspace') . ' has been deactivated.',
            'action_url' => route('workspace.index'),
            'workspace_id' => $member->workspace_id,
            'related_type' => WorkspaceMember::class,
            'related_id' => $member->id,
            'level' => 'warning',
        ]);
    }
}
