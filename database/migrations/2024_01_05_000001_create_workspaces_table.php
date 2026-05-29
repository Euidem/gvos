<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->string('workspace_code')->unique()->comment('Human-readable code e.g. WS-20240001');

            // Core relationships
            $table->foreignId('lead_request_id')->nullable()->constrained('lead_requests')->nullOnDelete();
            $table->foreignId('trial_id')->nullable()->constrained('trials')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('client_profile_id')->nullable()->constrained('client_profiles')->nullOnDelete();

            // Primary team members (denormalised for quick queries)
            $table->unsignedBigInteger('primary_manager_id')->nullable();
            $table->unsignedBigInteger('primary_talent_id')->nullable();

            // Meta
            $table->string('name')->comment('Display name for the workspace');
            $table->text('description')->nullable();

            // Status
            $table->enum('status', [
                'pending',
                'active',
                'paused',
                'completed',
                'cancelled',
            ])->default('pending');

            // Type
            $table->enum('type', ['trial', 'ongoing', 'project'])->default('trial');

            // Dates
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Limits (mirrored from trial or set by ops)
            $table->unsignedInteger('task_limit')->default(0)->comment('0 = unlimited');
            $table->unsignedInteger('file_limit_mb')->default(0)->comment('0 = unlimited');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // FKs to users table — must be explicit
            $table->foreign('primary_manager_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('primary_talent_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
