<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 17 — Weekly Report Automation
 *
 * Adds generation metadata to workspace_weekly_reports so the system can
 * record when a report was auto-generated from time logs and tasks, and by whom.
 *
 * Fields added:
 *   - generated_at          (timestamp nullable) — when generate was run
 *   - generated_by_user_id  (FK users nullable)  — actor who triggered generation
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspace_weekly_reports', function (Blueprint $table) {
            $table->timestamp('generated_at')->nullable()->after('published_at');
            $table->foreignId('generated_by_user_id')
                  ->nullable()
                  ->after('generated_at')
                  ->constrained('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workspace_weekly_reports', function (Blueprint $table) {
            $table->dropForeign(['generated_by_user_id']);
            $table->dropColumn(['generated_at', 'generated_by_user_id']);
        });
    }
};
