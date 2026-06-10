<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 18: Add billing enforcement tracking fields to workspace_subscriptions.
 *
 * These fields let the system track exactly when a subscription was restricted,
 * manually suspended, and reactivated — along with who took those actions and why.
 *
 * Status logic:
 *   - restricted_at IS NOT NULL AND suspended_at IS NULL → "restricted" (post-grace)
 *   - suspended_at IS NOT NULL (status = 'suspended')    → manually suspended by admin
 *   - reactivated_at IS NOT NULL                         → was previously restricted/suspended
 *
 * None of these columns are required to run the app — they are all nullable
 * so existing data is unaffected until enforcement actions are taken.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspace_subscriptions', function (Blueprint $table) {
            $table->timestamp('restricted_at')->nullable()->after('grace_ends_at')
                ->comment('Set when subscription enters restricted state (post-grace-period)');

            $table->timestamp('suspended_at')->nullable()->after('restricted_at')
                ->comment('Set when subscription is manually suspended by an admin');

            $table->timestamp('reactivated_at')->nullable()->after('suspended_at')
                ->comment('Set when a restricted/suspended subscription is reactivated');

            $table->text('restriction_reason')->nullable()->after('reactivated_at')
                ->comment('Admin note explaining the reason for restriction or suspension');

            $table->foreignId('suspended_by')
                ->nullable()
                ->after('restriction_reason')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin user who performed the manual suspension');

            $table->foreignId('reactivated_by')
                ->nullable()
                ->after('suspended_by')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Admin user who reactivated the subscription');
        });
    }

    public function down(): void
    {
        Schema::table('workspace_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropForeign(['reactivated_by']);
            $table->dropColumn([
                'restricted_at',
                'suspended_at',
                'reactivated_at',
                'restriction_reason',
                'suspended_by',
                'reactivated_by',
            ]);
        });
    }
};
