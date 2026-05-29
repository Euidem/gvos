<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('department.created', $this->record, [
            'name'       => $this->record->name,
            'company_id' => $this->record->company_id,
        ]);
    }
}
