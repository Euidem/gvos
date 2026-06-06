<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function afterSave(): void
    {
        $this->record->recalculateTotals();
        $this->record->save();

        AuditLogger::invoiceUpdated($this->record, ['updated_by' => auth()->id()]);
    }
}
