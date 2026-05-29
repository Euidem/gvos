<?php

namespace App\Filament\Resources\PriceEstimateResource\Pages;

use App\Filament\Resources\PriceEstimateResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPriceEstimate extends EditRecord
{
    protected static string $resource = PriceEstimateResource::class;

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
            AuditLogger::log('price_estimate.updated', $this->record, $changes);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
