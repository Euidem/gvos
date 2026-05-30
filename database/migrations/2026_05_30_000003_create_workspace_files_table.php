<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Create workspace_files table.
 *
 * Stores files uploaded to a workspace.
 * Files may optionally be linked to a specific workspace task.
 *
 * Storage:
 *   Files are stored on the local disk under storage/app/workspaces/{workspace_id}/
 *   They are NEVER publicly accessible — served only via WorkspaceFileController@download
 *   which verifies workspace access and visibility before streaming.
 *
 * Visibility:
 *   public   — visible to all active workspace members
 *   internal — visible only to admin, workspace_admin, and manager roles
 *
 * Categories (stored as freeform string; documented values):
 *   general, task_attachment, brief, deliverable, invoice_support, other
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_files', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workspace_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('uploaded_by_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('workspace_task_id')
                  ->nullable()
                  ->constrained('workspace_tasks')
                  ->nullOnDelete();

            $table->string('title')->nullable();           // user-provided label
            $table->string('original_filename');           // original name from upload
            $table->string('stored_filename');             // name on disk (UUID-based)
            $table->string('storage_path');                // relative path in local disk
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // bytes

            $table->enum('visibility', ['public', 'internal'])->default('public');
            $table->string('category')->nullable();        // see docs above
            $table->text('description')->nullable();

            $table->unsignedInteger('downloads_count')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['workspace_id', 'visibility']);
            $table->index('workspace_task_id');
            $table->index('uploaded_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_files');
    }
};
