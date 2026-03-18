<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('print_job_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_id');
            $table->foreign('job_id')->references('id')->on('print_jobs')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('print_agents')->nullOnDelete();
            $table->enum('status', ['success', 'fail']);
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['job_id']);
            $table->index(['agent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_job_attempts');
    }
};
