<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin',
            'operations_admin',
            'line_manager',
            'talent',
            'individual_client',
            'business_client_admin',
            'business_client_staff',
            'active_lead',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->command->info('GVOS roles seeded: ' . implode(', ', $roles));
    }
}
