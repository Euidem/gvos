<?php

namespace App\Notifications;

use App\Models\WorkspaceSubscription;

class BillingOverdueNotification extends GvosNotification
{
    public function __construct(WorkspaceSubscription $subscription)
    {
        $workspaceName = $subscription->workspace?->name ?? 'your workspace';
        $graceDays     = WorkspaceSubscription::GRACE_PERIOD_DAYS;
        $graceEnd      = $subscription->grace_ends_at?->format('d M Y');
        $gracePart     = $graceEnd ? " Access will be restricted on {$graceEnd} if the balance remains unpaid." : '';

        parent::__construct('billing_overdue', [
            'title'        => 'Payment overdue',
            'message'      => "An invoice for {$workspaceName} is overdue. A {$graceDays}-day grace period is in effect.{$gracePart} Please settle the outstanding balance to avoid workspace restrictions.",
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
