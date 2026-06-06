<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Invoice;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    protected ?string $oldStatus = null;

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function beforeSave(): void
    {
        $this->oldStatus = $this->record->getOriginal('status');
    }

    protected function afterSave(): void
    {
        if ($this->oldStatus !== 'confirmed' && $this->record->status === 'confirmed') {
            $this->record->confirm(auth()->id(), $this->record->confirmation_notes ?? '');
            AuditLogger::paymentConfirmed($this->record, ['confirmed_by' => auth()->id()]);
            app(NotificationService::class)->notifyPaymentRecorded($this->record->fresh(['workspace']), auth()->user());
            return;
        }

        if ($this->oldStatus !== $this->record->status
            && in_array($this->record->status, ['failed', 'cancelled'], true)) {
            AuditLogger::paymentFailedOrCancelled($this->record, ['actioned_by' => auth()->id()]);
        }
    }
}
