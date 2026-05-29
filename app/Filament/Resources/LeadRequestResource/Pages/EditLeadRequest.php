<?php

namespace App\Filament\Resources\LeadRequestResource\Pages;

use App\Filament\Resources\LeadRequestResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeadRequest extends EditRecord
{
    protected static string $resource = LeadRequestResource::class;

    /** Captured before save for before/after diffing. */
    protected array $beforeSnapshot = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->beforeSnapshot = $this->record->only(array_keys($data));
        return $data;
    }

    protected function afterSave(): void
    {
        $changes = [];
        foreach ($this->beforeSnapshot as $key => $before) {
            $after = $this->record->getAttribute($key);
            if ((string) $before !== (string) $after) {
                $changes[$key] = ['from' => $before, 'to' => $after];
            }
        }

        if (! empty($changes)) {
            // Log status change separately if status changed
            if (isset($changes['status'])) {
                AuditLogger::log('lead_request.status_changed', $this->record, [
                    'from' => $changes['status']['from'],
                    'to'   => $changes['status']['to'],
                ]);
            }

            AuditLogger::log('lead_request.updated', $this->record, $changes);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(false), // soft delete disabled
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
