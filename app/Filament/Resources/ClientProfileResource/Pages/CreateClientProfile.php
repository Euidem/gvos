<?php

namespace App\Filament\Resources\ClientProfileResource\Pages;

use App\Filament\Resources\ClientProfileResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateClientProfile extends CreateRecord
{
    protected static string $resource = ClientProfileResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('client_profile.created', $this->record, [
            'user_id'     => $this->record->user_id,
            'client_type' => $this->record->client_type,
            'status'      => $this->record->status,
        ]);
    }
}
