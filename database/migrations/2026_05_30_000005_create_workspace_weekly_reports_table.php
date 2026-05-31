<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->foreignId('prepared_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('total_minutes')->default(0);
            $table->longText('summary');
            $table->longText('achievements')->nullable();
            $table->longText('blockers')->nullable();
            $table->longText('next_steps')->nullable();
            $table->longText('client_notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'week_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_weekly_reports');
    }
};
