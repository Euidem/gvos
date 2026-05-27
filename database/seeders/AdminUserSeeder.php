<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seeds the first Super Admin user for local development.
     *
     * Credentials documented in /docs/CURRENT_STATUS.md
     * DO NOT use these credentials in production.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@gvos.local'],
            [
                'name' => 'GVOS Super Admin',
                'password' => Hash::make('password'),
                'timezone' => 'UTC',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');

        $this->command->info("Super Admin seeded: admin@gvos.local / password");
        $this->command->warn("⚠️  Change these credentials before any production use.");
    }
}
