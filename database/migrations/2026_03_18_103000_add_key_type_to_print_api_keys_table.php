<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_api_keys', function (Blueprint $table): void {
            $table->string('key_type', 16)->default('agent')->after('agent_id');
            $table->index(['tenant_id', 'key_type']);
        });
    }

    public function down(): void
    {
        Schema::table('print_api_keys', function (Blueprint $table): void {
            $table->dropIndex(['tenant_id', 'key_type']);
            $table->dropColumn('key_type');
        });
    }
};
