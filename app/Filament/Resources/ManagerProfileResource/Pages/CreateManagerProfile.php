<?php

namespace App\Filament\Resources\ManagerProfileResource\Pages;

use App\Filament\Resources\ManagerProfileResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateManagerProfile extends CreateRecord
{
    protected static string $resource = ManagerProfileResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('manager_profile.created', $this->record, [
            'user_id' => $this->record->user_id,
            'status'  => $this->record->status,
        ]);
    }
}
