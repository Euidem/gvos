<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('talent_code')->unique()->nullable();
            $table->string('role_type')->nullable();
            $table->text('skill_summary')->nullable();
            $table->enum('availability_type', ['fixed', 'flexible', 'hybrid'])->default('flexible');
            $table->unsignedSmallInteger('weekly_capacity_hours')->default(40);
            $table->string('work_timezone')->default('Africa/Lagos');
            $table->enum('training_status', [
                'not_started', 'in_training', 'prequalified', 'active', 'paused', 'suspended',
            ])->default('not_started');
            $table->enum('equipment_status', [
                'not_assigned', 'assigned', 'returned', 'damaged', 'maintenance',
            ])->default('not_assigned');
            $table->text('internal_notes')->nullable();
            $table->enum('status', ['active', 'pending', 'inactive', 'suspended'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_profiles');
    }
};
