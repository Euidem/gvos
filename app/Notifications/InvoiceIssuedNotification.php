<?php

namespace App\Notifications;

use App\Models\Invoice;

class InvoiceIssuedNotification extends GvosNotification
{
    public function __construct(Invoice $invoice)
    {
        parent::__construct('invoice_issued', [
            'title' => 'Invoice issued',
            'message' => 'Invoice ' . $invoice->invoice_number . ' has been issued.',
            'action_url' => route('workspace.billing.invoice', [$invoice->workspace_id, $invoice->id]),
            'workspace_id' => $invoice->workspace_id,
            'related_type' => Invoice::class,
            'related_id' => $invoice->id,
            'level' => 'info',
        ]);
    }
}
