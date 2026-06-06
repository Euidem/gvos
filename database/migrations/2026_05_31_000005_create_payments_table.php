<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference')->nullable()->unique();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('workspace_subscription_id')->nullable()->constrained('workspace_subscriptions')->nullOnDelete();
            $table->enum('provider', ['manual', 'bank_transfer', 'fincra', 'flutterwave', 'paystack', 'stripe', 'other'])->default('manual');
            $table->string('provider_reference')->nullable();
            $table->enum('currency', ['USD', 'GBP', 'EUR', 'NGN', 'CAD'])->default('USD');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'failed', 'reversed', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('confirmation_notes')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_id', 'status']);
            $table->index(['workspace_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
