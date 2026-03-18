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
        Schema::create('print_api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('print_tenants')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('print_agents')->nullOnDelete();
            $table->string('key_prefix', 20);
            $table->string('key_hash', 64);
            $table->json('scopes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->index(['tenant_id']);
            $table->index(['agent_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_api_keys');
    }
};
