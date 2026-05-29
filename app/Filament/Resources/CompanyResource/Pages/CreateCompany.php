<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('company.created', $this->record, [
            'name'   => $this->record->name,
            'type'   => $this->record->type,
            'status' => $this->record->status,
        ]);
    }
}
