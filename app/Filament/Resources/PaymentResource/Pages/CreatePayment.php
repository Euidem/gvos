<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Invoice;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::find($data['invoice_id']);

            if ($invoice) {
                $data['workspace_id'] = $data['workspace_id'] ?? $invoice->workspace_id;
                $data['workspace_subscription_id'] = $data['workspace_subscription_id'] ?? $invoice->workspace_subscription_id;
                $data['currency'] = $data['currency'] ?? $invoice->currency;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        AuditLogger::paymentRecorded($this->record, ['created_by' => auth()->id()]);

        if ($this->record->status === 'confirmed') {
            $this->record->confirm(auth()->id(), $this->record->confirmation_notes ?? '');
            AuditLogger::paymentConfirmed($this->record, ['confirmed_by' => auth()->id()]);
        }
    }
}
