<?php

namespace App\Notifications;

use App\Models\Trial;

class TrialApprovedNotification extends GvosNotification
{
    public function __construct(Trial $trial)
    {
        parent::__construct('trial_approved', [
            'title' => 'Trial approved',
            'message' => 'Your GVOS trial has been approved. You can review your next steps from your dashboard.',
            'action_url' => route('lead.dashboard'),
            'related_type' => Trial::class,
            'related_id' => $trial->id,
            'level' => 'success',
        ]);
    }
}
