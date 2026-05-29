<?php

namespace App\Filament\Resources\ManagerProfileResource\Pages;

use App\Filament\Resources\ManagerProfileResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditManagerProfile extends EditRecord
{
    protected static string $resource = ManagerProfileResource::class;

    private array $snapshotBefore = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->snapshotBefore = [
            'status'         => $this->record->status,
            'current_load'   => $this->record->current_load,
            'capacity_limit' => $this->record->capacity_limit,
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

        AuditLogger::log('manager_profile.updated', $this->record, $changes ?: ['user_id' => $this->record->user_id]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
