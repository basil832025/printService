<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PrintAgent;
use App\Models\PrintApiKey;
use App\Models\PrintJob;
use App\Models\PrintJobAttempt;
use App\Models\PrintTenant;
use App\Services\ActivationCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentQueueController extends Controller
{
    public function activate(Request $request, ActivationCodeService $activationCodes)
    {
        $data = $request->validate([
            'activation_code' => ['required', 'string', 'max:4096'],
            'machine_uid' => ['required', 'string', 'max:191'],
            'hostname' => ['required', 'string', 'max:255'],
            'os' => ['required', 'string', 'max:255'],
            'agent_version' => ['required', 'string', 'max:64'],
        ]);

        try {
            $payload = $activationCodes->decode($data['activation_code']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $tenantCode = (string) ($payload['tenant_code'] ?? '');
        $agentKey = (string) ($payload['agent_key'] ?? '');

        if ($tenantCode === '' || $agentKey === '') {
            return response()->json(['message' => 'Activation payload is missing required fields.'], 422);
        }

        $tenant = PrintTenant::query()->where('code', $tenantCode)->first();
        if (! $tenant) {
            return response()->json(['message' => 'Tenant is not found.'], 422);
        }

        $apiKey = PrintApiKey::query()
            ->where('tenant_id', $tenant->id)
            ->where('key_hash', hash('sha256', $agentKey))
            ->where('key_type', 'agent')
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $apiKey) {
            return response()->json(['message' => 'API key is invalid or expired.'], 422);
        }

        try {
            $activationCodes->consumeOneTimeToken($payload, (int) $tenant->id);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $machineUid = trim($data['machine_uid']);
        $agent = PrintAgent::query()
            ->where('tenant_id', $tenant->id)
            ->where('machine_uid', $machineUid)
            ->first();

        if (! $agent) {
            $agent = PrintAgent::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $data['hostname'],
                'machine_uid' => $machineUid,
                'status' => 'offline',
                'os_info' => $data['os'],
                'version' => $data['agent_version'],
            ]);
        }

        $agent->update([
            'name' => $data['hostname'],
            'os_info' => $data['os'],
            'version' => $data['agent_version'],
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        if ((int) ($apiKey->agent_id ?? 0) !== (int) $agent->id) {
            $apiKey->update(['agent_id' => $agent->id]);
        }

        return response()->json([
            'ok' => true,
            'api_base_url' => (string) ($payload['api_base_url'] ?? url('/api/print/v1')),
            'tenant_code' => $tenantCode,
            'agent_id' => $agent->id,
            'agent_key' => $agentKey,
        ]);
    }

    public function next(Request $request)
    {
        /** @var PrintApiKey|null $apiKey */
        $apiKey = $request->attributes->get('print_api_key');
        /** @var PrintAgent|null $agent */
        $agent = $request->attributes->get('print_agent');

        if (! $apiKey || ! $agent) {
            return response()->json(['message' => 'Agent binding is not configured for this key.'], 403);
        }

        $job = DB::transaction(function () use ($apiKey, $agent) {
            $now = now();

            $job = PrintJob::query()
                ->where('tenant_id', $apiKey->tenant_id)
                ->where(function ($query) use ($now) {
                    $query->where('status', 'queued')
                        ->orWhere(function ($retry) use ($now) {
                            $retry->where('status', 'retry_wait')
                                ->where(function ($ready) use ($now) {
                                    $ready->whereNull('next_retry_at')->orWhere('next_retry_at', '<=', $now);
                                });
                        });
                })
                ->orderByDesc('priority')
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if (! $job) {
                return null;
            }

            $job->status = 'reserved';
            $job->reserved_by_agent_id = $agent->id;
            $job->reserved_until = now()->addSeconds(60);
            $job->save();

            return $job;
        });

        if (! $job) {
            return response()->json(['job' => null]);
        }

        return response()->json([
            'job' => [
                'job_id' => $job->id,
                'printer_selector' => $job->printer_selector,
                'job_type' => $job->job_type,
                'content_type' => $job->content_type,
                'payload' => $job->payload,
                'copies' => $job->copies,
                'reserved_until' => optional($job->reserved_until)->toISOString(),
            ],
        ]);
    }

    public function ack(Request $request, int $agentId, string $jobId)
    {
        $agent = $request->attributes->get('print_agent');
        if (! $agent || (int) $agent->id !== $agentId) {
            return response()->json(['message' => 'Agent mismatch.'], 403);
        }

        $data = $request->validate([
            'printed_at' => ['required', 'date'],
            'device_info' => ['nullable', 'string', 'max:255'],
        ]);

        $job = PrintJob::query()->where('id', $jobId)->where('reserved_by_agent_id', $agent->id)->firstOrFail();

        $job->update([
            'status' => 'printed',
            'printed_at' => $data['printed_at'],
            'error_code' => null,
            'error_message' => null,
            'reserved_until' => null,
        ]);

        PrintJobAttempt::query()->create([
            'job_id' => $job->id,
            'agent_id' => $agent->id,
            'status' => 'success',
            'created_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function fail(Request $request, int $agentId, string $jobId)
    {
        $agent = $request->attributes->get('print_agent');
        if (! $agent || (int) $agent->id !== $agentId) {
            return response()->json(['message' => 'Agent mismatch.'], 403);
        }

        $data = $request->validate([
            'failed_at' => ['required', 'date'],
            'error_code' => ['required', 'string', 'max:100'],
            'error_message' => ['required', 'string'],
            'retryable' => ['nullable', 'boolean'],
        ]);

        $job = PrintJob::query()->where('id', $jobId)->where('reserved_by_agent_id', $agent->id)->firstOrFail();
        $attempts = $job->attempts_count + 1;
        $retryable = (bool) ($data['retryable'] ?? true);

        $retryDelays = [30, 120, 300, 900];
        $maxAttempts = count($retryDelays);

        $nextStatus = 'failed';
        $nextRetryAt = null;

        if ($retryable && $attempts <= $maxAttempts) {
            $nextStatus = 'retry_wait';
            $nextRetryAt = now()->addSeconds($retryDelays[$attempts - 1]);
        }

        $job->update([
            'status' => $nextStatus,
            'attempts_count' => $attempts,
            'error_code' => $data['error_code'],
            'error_message' => $data['error_message'],
            'next_retry_at' => $nextRetryAt,
            'reserved_by_agent_id' => null,
            'reserved_until' => null,
        ]);

        PrintJobAttempt::query()->create([
            'job_id' => $job->id,
            'agent_id' => $agent->id,
            'status' => 'fail',
            'error_code' => $data['error_code'],
            'error_message' => $data['error_message'],
            'created_at' => $data['failed_at'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function heartbeat(Request $request, int $agentId)
    {
        $agent = $request->attributes->get('print_agent');
        if (! $agent || (int) $agent->id !== $agentId) {
            return response()->json(['message' => 'Agent mismatch.'], 403);
        }

        $data = $request->validate([
            'agent_version' => ['required', 'string', 'max:64'],
            'os' => ['required', 'string', 'max:255'],
            'hostname' => ['required', 'string', 'max:255'],
            'printers' => ['nullable', 'array'],
            'printers.*' => ['string', 'max:255'],
        ]);

        $agent->update([
            'name' => $data['hostname'],
            'version' => $data['agent_version'],
            'os_info' => $data['os'],
            'status' => 'online',
            'last_seen_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
