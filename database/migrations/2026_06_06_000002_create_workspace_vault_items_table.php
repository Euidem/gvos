<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create workspace_vault_items table.
 *
 * Stores encrypted credential records scoped to a workspace.
 * The secret_value column is encrypted/decrypted by the model cast and must
 * never be exposed in list views, dashboards, audit logs, or access logs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_vault_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workspace_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->string('title');
            $table->string('category', 100)->nullable();
            $table->string('login_url', 2048)->nullable();
            $table->string('username')->nullable();
            $table->text('secret_value');
            $table->text('notes')->nullable();

            $table->string('visibility')->default('restricted');
            $table->string('status')->default('active');
            $table->json('allowed_roles')->nullable();
            $table->json('allowed_user_ids')->nullable();

            $table->timestamp('last_revealed_at')->nullable();
            $table->foreignId('last_revealed_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'visibility']);
            $table->index('created_by');
            $table->index('last_revealed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_vault_items');
    }
};
