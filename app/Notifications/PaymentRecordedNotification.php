<?php

namespace App\Notifications;

use App\Models\Payment;

class PaymentRecordedNotification extends GvosNotification
{
    public function __construct(Payment $payment)
    {
        $confirmed = $payment->status === 'confirmed';

        parent::__construct('payment_recorded', [
            'title' => $confirmed ? 'Payment confirmed' : 'Payment recorded',
            'message' => ($confirmed ? 'A payment has been confirmed.' : 'A payment has been recorded.')
                . ($payment->payment_reference ? ' Reference: ' . $payment->payment_reference . '.' : ''),
            'action_url' => $payment->workspace_id
                ? route('workspace.billing.payments', $payment->workspace_id)
                : route('workspace.index'),
            'workspace_id' => $payment->workspace_id,
            'related_type' => Payment::class,
            'related_id' => $payment->id,
            'level' => $confirmed ? 'success' : 'info',
        ]);
    }
}
