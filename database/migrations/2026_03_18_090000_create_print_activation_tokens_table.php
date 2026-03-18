<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_activation_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('print_tenants')->cascadeOnDelete();
            $table->foreignId('api_key_id')->constrained('print_api_keys')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('print_agents')->nullOnDelete();
            $table->string('kid', 32);
            $table->string('jti_hash', 64)->unique();
            $table->boolean('one_time')->default(true);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
            $table->index(['api_key_id']);
            $table->index(['agent_id']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_activation_tokens');
    }
};
