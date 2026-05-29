<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Immutable audit trail. Rows are never updated or deleted by the app.
     * Phase 1 logs: user created/updated, role changed, status changed,
     * password changed, profile updated, login.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // null when the action is system-initiated
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // dot-namespaced action, e.g. user.created, user.role_changed
            $table->string('action', 100)->index();
            // polymorphic subject (nullable for actions with no specific model target)
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->index(['subject_type', 'subject_id']);
            // serialised context snapshot — who, what, from→to values
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            // only created_at — audit rows are immutable
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
