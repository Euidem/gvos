<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('billing_plan_id')->nullable()->constrained('billing_plans')->nullOnDelete();
            $table->foreignId('client_profile_id')->nullable()->constrained('client_profiles')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->enum('currency', ['USD', 'GBP', 'EUR', 'NGN', 'CAD'])->default('USD');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('billing_cycle', ['bi_weekly', 'monthly', 'one_time'])->default('bi_weekly');
            $table->enum('status', ['trial', 'active', 'payment_due', 'overdue', 'suspended', 'cancelled', 'ended'])->default('trial');
            $table->date('starts_at')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamp('last_paid_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_subscriptions');
    }
};
