<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_status');
            $table->string('last_onboarding_step', 100)->nullable()->after('onboarding_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['onboarding_completed_at', 'last_onboarding_step']);
        });
    }
};
