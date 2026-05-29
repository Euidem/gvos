<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** Stores the role slug extracted before save. */
    private string $roleToAssign = '';

    /**
     * Strip 'role' from the data array so it isn't passed to User::create().
     * We'll handle role assignment in afterCreate().
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleToAssign = $data['role'] ?? '';
        unset($data['role']);
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Ensure a profile row exists for every created user
        $record->profile()->firstOrCreate(['user_id' => $record->id]);

        return $record;
    }

    protected function afterCreate(): void
    {
        if ($this->roleToAssign) {
            $this->record->syncRoles([$this->roleToAssign]);
        }

        AuditLogger::userCreated($this->record, ['role' => $this->roleToAssign]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
