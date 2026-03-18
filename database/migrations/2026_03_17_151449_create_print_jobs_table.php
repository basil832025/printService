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
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->constrained('print_tenants')->cascadeOnDelete();
            $table->string('source_system', 64)->nullable();
            $table->string('printer_selector');
            $table->enum('job_type', ['raw'])->default('raw');
            $table->string('content_type', 100);
            $table->longText('payload');
            $table->unsignedInteger('copies')->default(1);
            $table->unsignedInteger('priority')->default(50);
            $table->enum('status', ['queued', 'reserved', 'printing', 'printed', 'failed', 'retry_wait'])->default('queued');
            $table->string('idempotency_key', 190)->nullable();
            $table->foreignId('reserved_by_agent_id')->nullable()->constrained('print_agents')->nullOnDelete();
            $table->timestamp('reserved_until')->nullable();
            $table->unsignedInteger('attempts_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status', 'priority', 'created_at'], 'print_jobs_tenant_status_priority_idx');
            $table->index(['status', 'next_retry_at'], 'print_jobs_retry_idx');
            $table->index(['reserved_until'], 'print_jobs_reserved_until_idx');
            $table->index(['tenant_id', 'idempotency_key'], 'print_jobs_idempotency_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
