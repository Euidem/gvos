<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Expand workspace_members.role enum to support the full GVOS workspace role model.
 *
 * Roles added:
 *   workspace_admin — a designated workspace-level admin (can edit tasks, broad moves)
 *   client_admin    — individual client or business client admin (can approve/review)
 *   client_staff    — business client staff (view + comment only; cannot approve)
 *
 * Roles kept for backwards compatibility:
 *   client   — legacy value; treated as client_admin in application code
 *   talent   — talent/contractor working in the workspace
 *   manager  — GVOS line manager overseeing the workspace
 *   observer — read-only access
 *
 * Existing rows are safe: 'client', 'talent', 'manager', 'observer' remain valid enum values.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `workspace_members`
            MODIFY COLUMN `role` ENUM(
                'workspace_admin',
                'manager',
                'talent',
                'client_admin',
                'client_staff',
                'client',
                'observer'
            ) NOT NULL DEFAULT 'client'
        ");
    }

    public function down(): void
    {
        // WARNING: revert only works if no rows contain the new enum values.
        // Update or remove any workspace_admin / client_admin / client_staff rows first.
        DB::statement("
            ALTER TABLE `workspace_members`
            MODIFY COLUMN `role` ENUM(
                'client',
                'talent',
                'manager',
                'observer'
            ) NOT NULL DEFAULT 'client'
        ");
    }
};
