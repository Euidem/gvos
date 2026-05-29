<?php

namespace App\Filament\Resources\LeadRequestResource\Pages;

use App\Filament\Resources\LeadRequestResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateLeadRequest extends CreateRecord
{
    protected static string $resource = LeadRequestResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('lead_request.created', $this->record, [
            'email'       => $this->record->email,
            'client_type' => $this->record->client_type,
            'role_needed' => $this->record->role_needed,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
