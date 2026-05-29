<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_request_id')->constrained()->cascadeOnDelete();
            $table->enum('currency', ['USD', 'GBP', 'EUR', 'NGN'])->default('USD');
            $table->decimal('estimated_amount', 10, 2);
            $table->enum('billing_cycle', ['bi_weekly', 'monthly'])->default('monthly');
            $table->unsignedSmallInteger('estimated_hours_per_week')->nullable();
            $table->string('role_needed')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_estimates');
    }
};
