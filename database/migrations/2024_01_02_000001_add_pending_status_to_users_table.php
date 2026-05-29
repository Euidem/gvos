<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'pending' to the users.status enum.
     * Phase 1 — accounts created by admins start as pending until confirmed.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active','suspended','inactive','pending') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        // Convert any 'pending' rows to 'inactive' before shrinking the enum
        DB::table('users')->where('status', 'pending')->update(['status' => 'inactive']);

        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active','suspended','inactive') NOT NULL DEFAULT 'active'");
    }
};
