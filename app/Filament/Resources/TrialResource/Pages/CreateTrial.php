<?php

namespace App\Filament\Resources\TrialResource\Pages;

use App\Filament\Resources\TrialResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateTrial extends CreateRecord
{
    protected static string $resource = TrialResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('trial.created', $this->record, [
            'lead_request_id' => $this->record->lead_request_id,
            'status'          => $this->record->status,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
