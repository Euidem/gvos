<?php

namespace App\Notifications;

use App\Models\WorkspaceSubscription;

class WorkspaceRestrictedNotification extends GvosNotification
{
    public function __construct(WorkspaceSubscription $subscription)
    {
        $workspaceName = $subscription->workspace?->name ?? 'your workspace';

        parent::__construct('workspace_restricted', [
            'title'        => 'Workspace access restricted',
            'message'      => "Access to {$workspaceName} has been restricted due to an overdue invoice. Internal operations continue normally. Client access is limited until the outstanding balance is resolved.",
            'action_url'   => $subscription->workspace_id
                ? route('workspace.billing.index', $subscription->workspace_id)
                : null,
            'workspace_id' => $subscription->workspace_id,
            'related_type' => WorkspaceSubscription::class,
            'related_id'   => $subscription->id,
            'level'        => 'error',
        ]);
    }
}
