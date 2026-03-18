<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PrintApiKey;
use App\Models\PrintJob;
use App\Models\PrintTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PrintJobController extends Controller
{
    public function store(Request $request)
    {
        /** @var PrintApiKey|null $apiKey */
        $apiKey = $request->attributes->get('print_api_key');

        if (! $apiKey) {
            return response()->json(['message' => 'Site API key context is missing.'], 401);
        }

        $data = $request->validate([
            'tenant_code' => ['nullable', 'string', 'max:64'],
            'printer_selector' => ['required', 'string', 'max:255'],
            'job_type' => ['required', 'in:raw'],
            'content_type' => ['required', 'string', 'max:100'],
            'payload' => ['required', 'string'],
            'copies' => ['nullable', 'integer', 'min:1'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:100'],
            'idempotency_key' => ['nullable', 'string', 'max:190'],
        ]);

        $tenant = PrintTenant::query()
            ->where('id', $apiKey->tenant_id)
            ->where('is_active', true)
            ->firstOrFail();

        // For site API keys tenant is resolved by key ownership.
        // Ignore provided tenant_code to keep backward compatibility with existing clients.

        if (! empty($data['idempotency_key'])) {
            $existing = PrintJob::query()
                ->where('tenant_id', $tenant->id)
                ->where('idempotency_key', $data['idempotency_key'])
                ->first();

            if ($existing) {
                return response()->json([
                    'job_id' => $existing->id,
                    'status' => $existing->status,
                ]);
            }
        }

        $job = PrintJob::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenant->id,
            'printer_selector' => $data['printer_selector'],
            'job_type' => $data['job_type'],
            'content_type' => $data['content_type'],
            'payload' => $data['payload'],
            'copies' => (int) ($data['copies'] ?? 1),
            'priority' => (int) ($data['priority'] ?? 50),
            'status' => 'queued',
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);

        return response()->json([
            'job_id' => $job->id,
            'status' => $job->status,
        ]);
    }
}
