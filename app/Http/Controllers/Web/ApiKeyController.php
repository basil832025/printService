<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PrintApiKey;
use App\Models\PrintTenant;
use App\Services\ActivationCodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        $tenantId = Auth::user()?->tenant_id;

        return view('portal.api-keys', [
            'apiKeys' => PrintApiKey::query()->where('tenant_id', $tenantId)->latest('created_at')->get(),
            'agentApiKeys' => PrintApiKey::query()->where('tenant_id', $tenantId)->where('key_type', 'agent')->latest('created_at')->get(),
            'siteApiKeys' => PrintApiKey::query()->where('tenant_id', $tenantId)->where('key_type', 'site')->latest('created_at')->get(),
            'apiBaseUrl' => url('/api/print/v1'),
        ]);
    }

    public function store(Request $request, ActivationCodeService $activationCodes): RedirectResponse
    {
        $tenantId = Auth::user()?->tenant_id;
        $tenant = PrintTenant::query()->whereKey($tenantId)->first();
        abort_unless($tenant, 422, 'Tenant is not found.');

        $agentId = null;

        $plainKey = 'psk_'.Str::lower(Str::random(48));

        $apiKey = PrintApiKey::query()->create([
            'tenant_id' => $tenantId,
            'agent_id' => $agentId,
            'key_type' => 'agent',
            'key_prefix' => substr($plainKey, 0, 14),
            'key_hash' => hash('sha256', $plainKey),
            'scopes' => ['agent:poll', 'agent:ack', 'agent:fail', 'agent:heartbeat'],
            'expires_at' => null,
        ]);

        $activation = $activationCodes->generate(
            apiKey: $apiKey,
            plainAgentKey: $plainKey,
            apiBaseUrl: url('/api/print/v1'),
            tenantCode: $tenant->code,
            agentId: $agentId,
            ttlHours: (int) config('activation.default_ttl_hours', 24),
            oneTime: true,
        );

        return back()
            ->with('new_activation_code', $activation['code'])
            ->with('new_activation_code_exp', $activation['expires_at']?->format('Y-m-d H:i:s'))
            ->with('new_activation_code_kid', $activation['kid'])
            ->with('new_activation_code_one_time', $activation['one_time'] ? 'yes' : 'no');
    }

    public function storeSite(Request $request): RedirectResponse
    {
        $tenantId = Auth::user()?->tenant_id;
        $tenant = PrintTenant::query()->whereKey($tenantId)->first();
        abort_unless($tenant, 422, 'Tenant is not found.');

        $plainKey = 'pss_'.Str::lower(Str::random(48));

        PrintApiKey::query()->create([
            'tenant_id' => $tenantId,
            'agent_id' => null,
            'key_type' => 'site',
            'key_prefix' => substr($plainKey, 0, 14),
            'key_hash' => hash('sha256', $plainKey),
            'scopes' => ['site:jobs:create'],
            'expires_at' => null,
        ]);

        return back()->with('new_site_api_key', $plainKey);
    }

    public function destroy(PrintApiKey $apiKey): RedirectResponse
    {
        $tenantId = Auth::user()?->tenant_id;
        abort_unless((int) $apiKey->tenant_id === (int) $tenantId, 403);

        $apiKey->update(['revoked_at' => now()]);

        return back()->with('status', 'API key revoked.');
    }
}
