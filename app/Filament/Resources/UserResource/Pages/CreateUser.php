<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\ClientProfile;
use App\Models\ManagerProfile;
use App\Models\TalentProfile;
use App\Services\AuditLogger;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // Fields extracted from form data before User::create() is called
    private string  $roleToAssign = '';
    private ?string $firstName    = null;
    private ?string $lastName     = null;

    /**
     * Strip non-User-model fields from data before the record is created.
     * Store them as properties so afterCreate() can use them.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleToAssign = $data['role']       ?? '';
        $this->firstName    = $data['first_name'] ?? null;
        $this->lastName     = $data['last_name']  ?? null;

        unset($data['role'], $data['first_name'], $data['last_name']);

        // Auto-generate display name from first + last if the admin left it blank
        if (empty(trim($data['name'] ?? '')) && ($this->firstName || $this->lastName)) {
            $data['name'] = trim("{$this->firstName} {$this->lastName}");
        }

        // Ensure name is never empty
        if (empty(trim($data['name'] ?? ''))) {
            $data['name'] = $data['email'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // 1. Assign role
        if ($this->roleToAssign) {
            $this->record->syncRoles([$this->roleToAssign]);
        }

        // 2. Create / update the profile with first & last name
        $this->record->profile()->updateOrCreate(
            ['user_id' => $this->record->id],
            [
                'first_name' => $this->firstName,
                'last_name'  => $this->lastName,
            ]
        );

        // 3. Create stub profile record for role-specific profiles
        match ($this->roleToAssign) {
            'talent' => TalentProfile::firstOrCreate(
                ['user_id' => $this->record->id],
                ['status' => 'pending', 'training_status' => 'not_started', 'equipment_status' => 'not_assigned']
            ),
            'line_manager' => ManagerProfile::firstOrCreate(
                ['user_id' => $this->record->id],
                ['status' => 'pending', 'capacity_limit' => 10, 'current_load' => 0]
            ),
            'individual_client', 'business_client_admin', 'business_client_staff' => ClientProfile::firstOrCreate(
                ['user_id' => $this->record->id],
                [
                    'client_type' => match ($this->roleToAssign) {
                        'business_client_admin'  => 'business_admin',
                        'business_client_staff'  => 'business_staff',
                        default                  => 'individual',
                    },
                    'status' => 'pending',
                ]
            ),
            default => null,
        };

        // 4. Audit log
        AuditLogger::userCreated($this->record, [
            'first_name' => $this->firstName,
            'last_name'  => $this->lastName,
            'role'       => $this->roleToAssign,
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
