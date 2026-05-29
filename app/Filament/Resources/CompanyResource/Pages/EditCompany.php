<?php

namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    private array $snapshotBefore = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->snapshotBefore = [
            'name'   => $this->record->name,
            'status' => $this->record->status,
            'type'   => $this->record->type,
        ];
        return $data;
    }

    protected function afterSave(): void
    {
        $changes = [];
        foreach ($this->snapshotBefore as $field => $oldValue) {
            $newValue = $this->record->$field ?? null;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
            }
        }

        AuditLogger::log('company.updated', $this->record, $changes ?: ['name' => $this->record->name]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
