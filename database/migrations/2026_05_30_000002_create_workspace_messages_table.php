<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create workspace_messages table.
 *
 * Stores the main conversation thread inside each workspace.
 * Visibility:
 *   public   — visible to all active workspace members
 *   internal — visible only to admin, workspace_admin, and manager roles
 *
 * parent_id supports simple reply threading; complex UI is left for a later phase.
 * message_type = 'system' is reserved for automated event notices.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workspace_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('workspace_messages')
                  ->nullOnDelete();

            $table->longText('message');

            $table->enum('visibility', ['public', 'internal'])->default('public');
            $table->enum('message_type', ['text', 'system'])->default('text');

            $table->timestamp('edited_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes for efficient filtering by workspace + visibility
            $table->index(['workspace_id', 'visibility']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_messages');
    }
};
