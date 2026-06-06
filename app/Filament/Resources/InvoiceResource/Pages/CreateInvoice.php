<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function afterCreate(): void
    {
        $this->record->recalculateTotals();
        $this->record->save();

        AuditLogger::invoiceCreated($this->record, ['created_by' => auth()->id()]);
    }
}
