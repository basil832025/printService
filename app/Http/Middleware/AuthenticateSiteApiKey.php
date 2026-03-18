<?php

namespace App\Http\Middleware;

use App\Models\PrintApiKey;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSiteApiKey
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = trim((string) $request->bearerToken());
        if ($token === '') {
            return $this->unauthorized('Missing Bearer API key.');
        }

        $apiKey = PrintApiKey::query()
            ->with('tenant')
            ->where('key_hash', hash('sha256', $token))
            ->where('key_type', 'site')
            ->whereNull('revoked_at')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();

        if (! $apiKey) {
            return $this->unauthorized('Invalid site API key.');
        }

        $scopes = (array) ($apiKey->scopes ?? []);
        if (! in_array('site:jobs:create', $scopes, true)) {
            return $this->unauthorized('API key has no site:jobs:create scope.');
        }

        $request->attributes->set('print_api_key', $apiKey);
        $request->attributes->set('print_tenant', $apiKey->tenant);

        return $next($request);
    }

    private function unauthorized(string $message): JsonResponse
    {
        return response()->json(['message' => $message], Response::HTTP_UNAUTHORIZED);
    }
}
