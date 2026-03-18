<?php

namespace Tests\Feature;

use App\Models\PrintAgent;
use App\Models\PrintJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrintApiFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\PrintServiceBootstrapSeeder::class);
    }

    public function test_it_creates_print_job(): void
    {
        $response = $this->postJson('/api/print/v1/jobs', [
            'tenant_code' => 'default',
            'printer_selector' => 'cashdesk_1',
            'job_type' => 'raw',
            'content_type' => 'text/plain',
            'payload' => "Hello printer\n",
            'copies' => 1,
            'priority' => 50,
            'idempotency_key' => 'test-order-1001',
        ]);

        $response->assertOk()->assertJsonStructure([
            'job_id',
            'status',
        ]);

        $this->assertDatabaseCount('print_jobs', 1);
        $this->assertDatabaseHas('print_jobs', [
            'status' => 'queued',
            'printer_selector' => 'cashdesk_1',
        ]);
    }

    public function test_agent_can_poll_and_ack_job(): void
    {
        $create = $this->postJson('/api/print/v1/jobs', [
            'tenant_code' => 'default',
            'printer_selector' => 'cashdesk_1',
            'job_type' => 'raw',
            'content_type' => 'text/plain',
            'payload' => "Receipt #2002\n",
        ])->assertOk();

        $jobId = (string) $create->json('job_id');
        $agent = PrintAgent::query()->firstOrFail();

        $pollPath = '/api/print/v1/agents/next';
        $pollHeaders = $this->signedHeaders('GET', $pollPath, '');

        $poll = $this->withHeaders($pollHeaders)->get($pollPath);

        $poll->assertOk()->assertJsonPath('job.job_id', $jobId);

        $ackPath = '/api/print/v1/agents/'.$agent->id.'/jobs/'.$jobId.'/ack';
        $ackBody = [
            'printed_at' => now()->toISOString(),
            'device_info' => 'test-agent',
        ];

        $ackHeaders = $this->signedHeaders('POST', $ackPath, json_encode($ackBody, JSON_THROW_ON_ERROR));

        $this->json('POST', $ackPath, $ackBody, $ackHeaders)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('print_jobs', [
            'id' => $jobId,
            'status' => 'printed',
        ]);
    }

    public function test_agent_can_fail_job_and_schedule_retry(): void
    {
        $create = $this->postJson('/api/print/v1/jobs', [
            'tenant_code' => 'default',
            'printer_selector' => 'cashdesk_1',
            'job_type' => 'raw',
            'content_type' => 'text/plain',
            'payload' => "Receipt #3003\n",
        ])->assertOk();

        $jobId = (string) $create->json('job_id');
        $agent = PrintAgent::query()->firstOrFail();

        $pollPath = '/api/print/v1/agents/next';
        $pollHeaders = $this->signedHeaders('GET', $pollPath, '');
        $this->withHeaders($pollHeaders)->get($pollPath)->assertOk();

        $failPath = '/api/print/v1/agents/'.$agent->id.'/jobs/'.$jobId.'/fail';
        $failBody = [
            'failed_at' => now()->toISOString(),
            'error_code' => 'PRINTER_OFFLINE',
            'error_message' => 'Printer is temporarily unavailable',
            'retryable' => true,
        ];

        $failHeaders = $this->signedHeaders('POST', $failPath, json_encode($failBody, JSON_THROW_ON_ERROR));

        $this->json('POST', $failPath, $failBody, $failHeaders)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $job = PrintJob::query()->findOrFail($jobId);
        $this->assertSame('retry_wait', $job->status);
        $this->assertSame(1, $job->attempts_count);
        $this->assertNotNull($job->next_retry_at);
    }

    public function test_agent_can_fail_job_without_retry(): void
    {
        $create = $this->postJson('/api/print/v1/jobs', [
            'tenant_code' => 'default',
            'printer_selector' => 'cashdesk_1',
            'job_type' => 'raw',
            'content_type' => 'text/plain',
            'payload' => "Receipt #4004\n",
        ])->assertOk();

        $jobId = (string) $create->json('job_id');
        $agent = PrintAgent::query()->firstOrFail();

        $pollPath = '/api/print/v1/agents/next';
        $pollHeaders = $this->signedHeaders('GET', $pollPath, '');
        $this->withHeaders($pollHeaders)->get($pollPath)->assertOk();

        $failPath = '/api/print/v1/agents/'.$agent->id.'/jobs/'.$jobId.'/fail';
        $failBody = [
            'failed_at' => now()->toISOString(),
            'error_code' => 'INVALID_PAYLOAD',
            'error_message' => 'Payload can not be printed',
            'retryable' => false,
        ];

        $failHeaders = $this->signedHeaders('POST', $failPath, json_encode($failBody, JSON_THROW_ON_ERROR));

        $this->json('POST', $failPath, $failBody, $failHeaders)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $job = PrintJob::query()->findOrFail($jobId);
        $this->assertSame('failed', $job->status);
        $this->assertSame(1, $job->attempts_count);
        $this->assertNull($job->next_retry_at);
    }

    public function test_retryable_failures_eventually_mark_job_as_failed(): void
    {
        $create = $this->postJson('/api/print/v1/jobs', [
            'tenant_code' => 'default',
            'printer_selector' => 'cashdesk_1',
            'job_type' => 'raw',
            'content_type' => 'text/plain',
            'payload' => "Receipt #5005\n",
        ])->assertOk();

        $jobId = (string) $create->json('job_id');
        $agent = PrintAgent::query()->firstOrFail();

        $pollPath = '/api/print/v1/agents/next';
        $failPath = '/api/print/v1/agents/'.$agent->id.'/jobs/'.$jobId.'/fail';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $pollHeaders = $this->signedHeaders('GET', $pollPath, '');
            $this->withHeaders($pollHeaders)->get($pollPath)->assertOk();

            $failBody = [
                'failed_at' => now()->toISOString(),
                'error_code' => 'PRINTER_OFFLINE',
                'error_message' => 'Printer is temporarily unavailable',
                'retryable' => true,
            ];

            $failHeaders = $this->signedHeaders('POST', $failPath, json_encode($failBody, JSON_THROW_ON_ERROR));

            $this->json('POST', $failPath, $failBody, $failHeaders)
                ->assertOk()
                ->assertJson(['ok' => true]);

            $job = PrintJob::query()->findOrFail($jobId);

            if ($attempt < 5) {
                $this->assertSame('retry_wait', $job->status);
                $this->assertNotNull($job->next_retry_at);

                $job->update([
                    'next_retry_at' => now()->subSecond(),
                ]);

                continue;
            }

            $this->assertSame('failed', $job->status);
            $this->assertNull($job->next_retry_at);
        }

        $final = PrintJob::query()->findOrFail($jobId);
        $this->assertSame(5, $final->attempts_count);
        $this->assertSame('failed', $final->status);
    }

    /**
     * @return array<string, string>
     */
    private function signedHeaders(string $method, string $path, string $body): array
    {
        $agentKey = 'psk_local_dev_change_me';
        $timestamp = (string) now()->timestamp;
        $payloadHash = hash('sha256', $body);
        $nl = chr(10);
        $canonical = strtoupper($method).$nl.$path.$nl.$timestamp.$nl.$payloadHash;
        $signature = hash_hmac('sha256', $canonical, $agentKey);

        return [
            'X-Agent-Key' => $agentKey,
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
