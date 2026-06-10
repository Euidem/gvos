<?php

namespace App\Notifications;

use App\Models\WorkspaceSubscription;

class BillingDueSoonNotification extends GvosNotification
{
    public function __construct(WorkspaceSubscription $subscription)
    {
        $workspaceName = $subscription->workspace?->name ?? 'your workspace';
        $dueDate       = $subscription->next_billing_date?->format('d M Y') ?? 'soon';

        parent::__construct('billing_due_soon', [
            'title'        => 'Payment due soon',
            'message'      => "A payment is due on {$dueDate} for {$workspaceName}. Please ensure your account is settled to avoid service interruption.",
            'action_url'   => $subscription->workspace_id
                ? route('workspace.billing.index', $subscription->workspace_id)
                : null,
            'workspace_id' => $subscription->workspace_id,
            'related_type' => WorkspaceSubscription::class,
            'related_id'   => $subscription->id,
            'level'        => 'warning',
        ]);
    }
}
