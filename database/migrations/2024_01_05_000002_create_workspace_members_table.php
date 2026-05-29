<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained('workspaces')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id');

            $table->enum('role', [
                'client',
                'talent',
                'manager',
                'observer',
            ])->default('client')->comment('Role this user plays inside the workspace');

            $table->enum('status', [
                'active',
                'removed',
            ])->default('active');

            $table->timestamp('joined_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // FK to users
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // A user can only appear once per workspace
            $table->unique(['workspace_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_members');
    }
};
