<?php

namespace App\Filament\Resources\ClientProfileResource\Pages;

use App\Filament\Resources\ClientProfileResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientProfile extends EditRecord
{
    protected static string $resource = ClientProfileResource::class;

    private array $snapshotBefore = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->snapshotBefore = [
            'client_type' => $this->record->client_type,
            'status'      => $this->record->status,
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

        AuditLogger::log('client_profile.updated', $this->record, $changes ?: ['user_id' => $this->record->user_id]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
