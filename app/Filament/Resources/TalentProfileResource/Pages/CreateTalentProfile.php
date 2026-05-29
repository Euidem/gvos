<?php

namespace App\Filament\Resources\TalentProfileResource\Pages;

use App\Filament\Resources\TalentProfileResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;

class CreateTalentProfile extends CreateRecord
{
    protected static string $resource = TalentProfileResource::class;

    protected function afterCreate(): void
    {
        AuditLogger::log('talent_profile.created', $this->record, [
            'user_id'         => $this->record->user_id,
            'training_status' => $this->record->training_status,
            'status'          => $this->record->status,
        ]);
    }
}
