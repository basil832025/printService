<?php

namespace Database\Seeders;

use App\Models\PrintAgent;
use App\Models\PrintApiKey;
use App\Models\PrintTenant;
use Illuminate\Database\Seeder;

class PrintServiceBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = PrintTenant::query()->updateOrCreate(
            ['code' => 'default'],
            [
                'name' => 'Default Tenant',
                'is_active' => true,
            ]
        );

        $agent = PrintAgent::query()->updateOrCreate(
            ['machine_uid' => 'local-dev-agent'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'LOCAL-DEV-AGENT',
                'status' => 'offline',
                'os_info' => 'Windows 7 SP1 x86',
                'version' => '1.0.0',
            ]
        );

        $plainKey = (string) env('PRINT_AGENT_KEY', 'psk_local_dev_change_me');

        PrintApiKey::query()->updateOrCreate(
            ['key_hash' => hash('sha256', $plainKey)],
            [
                'tenant_id' => $tenant->id,
                'agent_id' => $agent->id,
                'key_type' => 'agent',
                'key_prefix' => substr($plainKey, 0, 12),
                'scopes' => ['agent:poll', 'agent:ack', 'agent:fail', 'agent:heartbeat'],
                'expires_at' => null,
                'revoked_at' => null,
            ]
        );
    }
}
