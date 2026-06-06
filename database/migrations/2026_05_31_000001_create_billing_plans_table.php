<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique();
            $table->text('description')->nullable();
            $table->enum('currency', ['USD', 'GBP', 'EUR', 'NGN', 'CAD'])->default('USD');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('billing_cycle', ['bi_weekly', 'monthly', 'one_time'])->default('bi_weekly');
            $table->integer('included_talents')->default(1);
            $table->integer('included_hours_per_week')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_plans');
    }
};
