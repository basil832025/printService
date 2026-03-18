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
        Schema::create('print_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('print_tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('machine_uid')->unique();
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->string('os_info')->nullable();
            $table->string('version', 64)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_agents');
    }
};
