<?php

namespace App\Http\Middleware;

use App\Models\PrintApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAgentSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $agentKey = trim((string) $request->header('X-Agent-Key', ''));
        $timestamp = trim((string) $request->header('X-Timestamp', ''));
        $signature = trim((string) $request->header('X-Signature', ''));

        if ($agentKey === '' || $timestamp === '' || $signature === '') {
            return $this->unauthorized('Missing agent signature headers.');
        }

        if (! ctype_digit($timestamp)) {
            return $this->unauthorized('Invalid signature timestamp.');
        }

        $now = Carbon::now()->timestamp;
        if (abs($now - (int) $timestamp) > 300) {
            return $this->unauthorized('Signature timestamp is out of allowed skew window.');
        }

        $keyHash = hash('sha256', $agentKey);
        $apiKey = PrintApiKey::query()
            ->with('agent')
            ->where('key_hash', $keyHash)
            ->where('key_type', 'agent')
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $apiKey) {
            return $this->unauthorized('Invalid agent key.');
        }

        $scopes = (array) ($apiKey->scopes ?? []);
        if (! in_array('agent:poll', $scopes, true)
            || ! in_array('agent:ack', $scopes, true)
            || ! in_array('agent:fail', $scopes, true)
            || ! in_array('agent:heartbeat', $scopes, true)) {
            return $this->unauthorized('Agent key scopes are invalid.');
        }

        $payloadHash = hash('sha256', (string) $request->getContent());
        $canonical = strtoupper($request->method())."\n"
            .$request->getPathInfo()."\n"
            .$timestamp."\n"
            .$payloadHash;

        $expected = hash_hmac('sha256', $canonical, $agentKey);

        if (! hash_equals($expected, $signature)) {
            return $this->unauthorized('Invalid request signature.');
        }

        $request->attributes->set('print_api_key', $apiKey);
        $request->attributes->set('print_agent', $apiKey->agent);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json(['message' => $message], Response::HTTP_UNAUTHORIZED);
    }
}
