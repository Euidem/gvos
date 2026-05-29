<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trials', function (Blueprint $table) {
            $table->id();
            $table->string('trial_code')->unique()->nullable();
            $table->foreignId('lead_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('active_lead_user_id')
                ->nullable()
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->foreignId('assigned_talent_user_id')
                ->nullable()
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->foreignId('assigned_manager_user_id')
                ->nullable()
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->foreignId('price_estimate_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->enum('status', [
                'pending', 'approved', 'active', 'completed',
                'expired', 'cancelled', 'converted',
            ])->default('pending')->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedSmallInteger('trial_duration_hours')->default(24);
            $table->unsignedSmallInteger('trial_task_limit')->default(3);
            $table->unsignedSmallInteger('trial_file_limit_mb')->default(100);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trials');
    }
};
