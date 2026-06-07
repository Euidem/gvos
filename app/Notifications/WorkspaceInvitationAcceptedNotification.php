<?php

namespace App\Notifications;

use App\Models\WorkspaceInvitation;

class WorkspaceInvitationAcceptedNotification extends GvosNotification
{
    public function __construct(WorkspaceInvitation $invitation)
    {
        $invitation->loadMissing('workspace', 'acceptedBy');

        parent::__construct('workspace_invitation_accepted', [
            'title' => 'Workspace invitation accepted',
            'message' => ($invitation->acceptedBy?->name ?? $invitation->email) . ' accepted the invitation to '
                . ($invitation->workspace?->name ?? 'a GVOS workspace') . '.',
            'action_url' => $invitation->workspace ? route('workspace.members.index', $invitation->workspace) : null,
            'workspace_id' => $invitation->workspace_id,
            'related_type' => WorkspaceInvitation::class,
            'related_id' => $invitation->id,
            'level' => 'success',
        ]);
    }
}
