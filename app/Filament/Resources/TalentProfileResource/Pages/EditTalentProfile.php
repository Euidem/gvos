<?php

namespace App\Filament\Resources\TalentProfileResource\Pages;

use App\Filament\Resources\TalentProfileResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTalentProfile extends EditRecord
{
    protected static string $resource = TalentProfileResource::class;

    private array $snapshotBefore = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->snapshotBefore = [
            'training_status'  => $this->record->training_status,
            'equipment_status' => $this->record->equipment_status,
            'status'           => $this->record->status,
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

        AuditLogger::log('talent_profile.updated', $this->record, $changes ?: ['user_id' => $this->record->user_id]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
