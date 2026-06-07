<?php

namespace App\Notifications;

use App\Models\WorkspaceMember;

class WorkspaceMemberAddedNotification extends GvosNotification
{
    public function __construct(WorkspaceMember $member)
    {
        $member->loadMissing('workspace');

        parent::__construct('workspace_member_added', [
            'title' => 'Workspace access added',
            'message' => 'You have been added to workspace ' . ($member->workspace?->name ?? 'GVOS workspace') . ' as ' . $member->roleLabel() . '.',
            'action_url' => $member->workspace ? route('workspace.show', $member->workspace) : null,
            'workspace_id' => $member->workspace_id,
            'related_type' => WorkspaceMember::class,
            'related_id' => $member->id,
            'level' => 'info',
        ]);
    }
}
