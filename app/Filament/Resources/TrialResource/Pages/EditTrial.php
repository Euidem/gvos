<?php

namespace App\Filament\Resources\TrialResource\Pages;

use App\Filament\Resources\TrialResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTrial extends EditRecord
{
    protected static string $resource = TrialResource::class;

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
            AuditLogger::log('trial.updated', $this->record, $changes);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->visible(false),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
