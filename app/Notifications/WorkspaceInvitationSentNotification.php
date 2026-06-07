<?php

namespace App\Notifications;

use App\Models\WorkspaceInvitation;

class WorkspaceInvitationSentNotification extends GvosNotification
{
    public function __construct(WorkspaceInvitation $invitation)
    {
        $invitation->loadMissing('workspace');

        parent::__construct('workspace_invitation_sent', [
            'title' => 'Workspace invitation sent',
            'message' => 'You have been invited to join workspace ' . ($invitation->workspace?->name ?? 'GVOS workspace') . '. Check your email for the secure acceptance link.',
            'action_url' => $invitation->workspace ? route('workspace.members.index', $invitation->workspace) : route('workspace.index'),
            'workspace_id' => $invitation->workspace_id,
            'related_type' => WorkspaceInvitation::class,
            'related_id' => $invitation->id,
            'level' => 'info',
        ]);
    }
}
