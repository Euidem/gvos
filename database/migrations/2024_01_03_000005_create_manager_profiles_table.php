<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manager_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('manager_code')->unique()->nullable();
            $table->string('department')->nullable();
            $table->unsignedSmallInteger('capacity_limit')->default(10);
            $table->unsignedSmallInteger('current_load')->default(0);
            $table->string('specialization')->nullable();
            $table->enum('status', ['active', 'pending', 'inactive', 'suspended'])->default('pending');
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_profiles');
    }
};
