<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create workspace_vault_access_logs table.
 *
 * Records metadata-only vault activity. This table must never store plaintext
 * secrets or credential payloads.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_vault_access_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workspace_vault_item_id')
                  ->constrained('workspace_vault_items')
                  ->cascadeOnDelete();

            $table->foreignId('workspace_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('action', 100);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['workspace_id', 'action']);
            $table->index(['workspace_vault_item_id', 'action']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_vault_access_logs');
    }
};
