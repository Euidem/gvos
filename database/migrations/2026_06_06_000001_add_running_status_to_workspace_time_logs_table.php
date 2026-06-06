<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE workspace_time_logs MODIFY status ENUM('draft', 'running', 'submitted', 'reviewed', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::table('workspace_time_logs')
            ->where('status', 'running')
            ->update(['status' => 'draft']);

        DB::statement("ALTER TABLE workspace_time_logs MODIFY status ENUM('draft', 'submitted', 'reviewed', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");
    }
};
