<?php

namespace App\Notifications;

use App\Models\WorkspaceMember;

class WorkspaceMemberRoleChangedNotification extends GvosNotification
{
    public function __construct(WorkspaceMember $member, string $oldRole)
    {
        $member->loadMissing('workspace');

        parent::__construct('workspace_role_changed', [
            'title' => 'Workspace role updated',
            'message' => 'Your role in ' . ($member->workspace?->name ?? 'this workspace') . ' changed from '
                . ucfirst(str_replace('_', ' ', $oldRole)) . ' to ' . $member->roleLabel() . '.',
            'action_url' => $member->workspace ? route('workspace.show', $member->workspace) : null,
            'workspace_id' => $member->workspace_id,
            'related_type' => WorkspaceMember::class,
            'related_id' => $member->id,
            'level' => 'info',
        ]);
    }
}
