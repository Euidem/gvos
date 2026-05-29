<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_requests', function (Blueprint $table) {
            $table->id();
            $table->string('lead_code')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('timezone')->nullable();
            $table->enum('client_type', ['individual', 'business'])->default('individual');
            $table->string('company_name')->nullable();
            $table->string('company_website')->nullable();
            $table->string('company_email_domain')->nullable();
            $table->string('role_needed')->nullable();
            $table->string('role_needed_other')->nullable();
            $table->unsignedSmallInteger('estimated_hours_per_week')->nullable();
            $table->date('preferred_start_date')->nullable();
            $table->string('preferred_work_schedule')->nullable();
            $table->text('required_skills')->nullable();
            $table->longText('work_description')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('source')->nullable();
            $table->enum('status', [
                'new',
                'price_estimated',
                'price_accepted',
                'under_review',
                'trial_approved',
                'trial_active',
                'trial_completed',
                'payment_pending',
                'converted',
                'lost',
                'disqualified',
            ])->default('new')->index();
            $table->longText('admin_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_requests');
    }
};
