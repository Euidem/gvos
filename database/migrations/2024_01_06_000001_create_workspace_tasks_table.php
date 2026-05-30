<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_code', 30)->nullable()->unique();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', [
                'pending',
                'in_progress',
                'blocked',
                'submitted',
                'revision_requested',
                'approved',
                'closed',
                'cancelled',
            ])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->text('internal_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'status']);
            $table->index('assigned_to_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_tasks');
    }
};
