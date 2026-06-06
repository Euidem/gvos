<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected ?string $oldStatus = null;

    protected function beforeSave(): void
    {
        $this->oldStatus = $this->record->getOriginal('status');
    }

    protected function afterSave(): void
    {
        $this->record->recalculateTotals();
        $this->record->save();

        AuditLogger::invoiceUpdated($this->record, ['updated_by' => auth()->id()]);

        if ($this->oldStatus !== 'issued' && $this->record->status === 'issued') {
            AuditLogger::invoiceIssued($this->record, ['actioned_by' => auth()->id()]);
            app(NotificationService::class)->notifyInvoiceIssued($this->record, auth()->user());
        }
    }
}
