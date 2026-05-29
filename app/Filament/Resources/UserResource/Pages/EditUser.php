<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\AuditLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** Stores the role slug extracted before save. */
    private string $roleToAssign = '';

    protected function getHeaderActions(): array
    {
        return [
            // Delete is intentionally omitted — use status changes instead.
        ];
    }

    /**
     * Strip 'role' from the data array so it isn't passed to User::save().
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleToAssign = $data['role'] ?? '';
        unset($data['role']);
        return $data;
    }

    protected function afterSave(): void
    {
        $previousRole = $this->record->getRoleNames()->first() ?? '';

        if ($this->roleToAssign && $this->roleToAssign !== $previousRole) {
            $this->record->syncRoles([$this->roleToAssign]);
            AuditLogger::roleChanged($this->record, $previousRole, $this->roleToAssign);
        } elseif ($this->roleToAssign) {
            $this->record->syncRoles([$this->roleToAssign]);
        }

        AuditLogger::userUpdated($this->record, $this->record->getDirty());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
