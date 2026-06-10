<?php

namespace App\Notifications;

use App\Models\WorkspaceSubscription;

class WorkspaceSuspendedNotification extends GvosNotification
{
    public function __construct(WorkspaceSubscription $subscription)
    {
        $workspaceName = $subscription->workspace?->name ?? 'your workspace';
        $reason        = $subscription->restriction_reason
            ? ' Reason: ' . $subscription->restriction_reason
            : '';

        parent::__construct('workspace_suspended', [
            'title'        => 'Workspace suspended',
            'message'      => "Access to {$workspaceName} has been suspended by a GVOS administrator.{$reason} Please contact the GVOS team or review your billing to restore access.",
            'action_url'   => $subscription->workspace_id
                ? route('workspace.billing.restricted', $subscription->workspace_id)
                : null,
            'workspace_id' => $subscription->workspace_id,
            'related_type' => WorkspaceSubscription::class,
            'related_id'   => $subscription->id,
            'level'        => 'error',
        ]);
    }
}
