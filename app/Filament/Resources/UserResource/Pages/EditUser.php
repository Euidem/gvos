<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Services\AuditLogger;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    // Fields extracted from form data before User::save() is called
    private string  $roleToAssign = '';
    private ?string $firstName    = null;
    private ?string $lastName     = null;

    // Snapshot of User fields taken BEFORE save (getDirty() returns empty after save)
    private array $snapshotBefore = [];

    protected function getHeaderActions(): array
    {
        return [
            // Delete is intentionally omitted — use status changes instead.
        ];
    }

    /**
     * Pre-fill the form with first_name / last_name from the related profile.
     * These fields do not live on the User model itself.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $profile = $this->record->profile;

        $data['first_name'] = $profile?->first_name;
        $data['last_name']  = $profile?->last_name;

        return $data;
    }

    /**
     * Strip non-User-model fields and capture a before-snapshot for the audit log.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Capture what the user is changing BEFORE the save happens.
        // getDirty() is empty after save so we must snapshot here.
        $this->snapshotBefore = [
            'name'     => $this->record->name,
            'email'    => $this->record->email,
            'status'   => $this->record->status,
            'timezone' => $this->record->timezone,
        ];

        $this->roleToAssign = $data['role']       ?? '';
        $this->firstName    = $data['first_name'] ?? null;
        $this->lastName     = $data['last_name']  ?? null;

        unset($data['role'], $data['first_name'], $data['last_name']);

        // Auto-generate display name from first + last if left blank
        if (empty(trim($data['name'] ?? '')) && ($this->firstName || $this->lastName)) {
            $data['name'] = trim("{$this->firstName} {$this->lastName}");
        }

        if (empty(trim($data['name'] ?? ''))) {
            $data['name'] = $this->record->email;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // 1. Role handling
        $previousRole = $this->record->getRoleNames()->first() ?? '';

        if ($this->roleToAssign) {
            $this->record->syncRoles([$this->roleToAssign]);

            if ($this->roleToAssign !== $previousRole) {
                AuditLogger::roleChanged($this->record, $previousRole, $this->roleToAssign);
            }
        }

        // 2. Profile update
        $this->record->profile()->updateOrCreate(
            ['user_id' => $this->record->id],
            [
                'first_name' => $this->firstName,
                'last_name'  => $this->lastName,
            ]
        );

        // 3. Build meaningful change context from the before-snapshot
        $changes = [];
        foreach ($this->snapshotBefore as $field => $oldValue) {
            $newValue = $this->record->$field;
            if ($newValue !== $oldValue) {
                $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
            }
        }

        // Also capture profile field changes
        $profile = $this->record->fresh()->profile;
        if ($profile) {
            if ($profile->first_name !== ($this->snapshotBefore['first_name'] ?? null)) {
                $changes['first_name'] = $this->firstName;
            }
            if ($profile->last_name !== ($this->snapshotBefore['last_name'] ?? null)) {
                $changes['last_name'] = $this->lastName;
            }
        }

        AuditLogger::userUpdated($this->record, array_merge($changes, [
            'role' => $this->roleToAssign,
        ]));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
