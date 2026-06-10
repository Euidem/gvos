<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notification_key', 100)->nullable()->index();
            $table->string('channel', 20)->default('mail');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_email_hash', 64)->nullable();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->string('status', 20)->default('success');
            $table->string('error_message', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_delivery_logs');
    }
};
