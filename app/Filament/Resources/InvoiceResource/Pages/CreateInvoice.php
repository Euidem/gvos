<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function afterCreate(): void
    {
        $this->record->recalculateTotals();
        $this->record->save();

        AuditLogger::invoiceCreated($this->record, ['created_by' => auth()->id()]);

        if ($this->record->status === 'issued') {
            AuditLogger::invoiceIssued($this->record, ['actioned_by' => auth()->id()]);
            app(NotificationService::class)->notifyInvoiceIssued($this->record, auth()->user());
        }
    }
}
