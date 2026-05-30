<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_task_id')->constrained('workspace_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->longText('comment');
            $table->enum('visibility', ['public', 'internal'])->default('public');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_task_id', 'visibility']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_task_comments');
    }
};
