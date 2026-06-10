<?php

namespace App\Notifications;

use App\Models\WorkspaceSubscription;

class WorkspaceReactivatedNotification extends GvosNotification
{
    public function __construct(WorkspaceSubscription $subscription)
    {
        $workspaceName = $subscription->workspace?->name ?? 'your workspace';

        parent::__construct('workspace_reactivated', [
            'title'        => 'Workspace access restored',
            'message'      => "Access to {$workspaceName} has been restored. All workspace features are now available.",
            'action_url'   => $subscription->workspace_id
                ? route('workspace.show', $subscription->workspace_id)
                : null,
            'workspace_id' => $subscription->workspace_id,
            'related_type' => WorkspaceSubscription::class,
            'related_id'   => $subscription->id,
            'level'        => 'success',
        ]);
    }
}
