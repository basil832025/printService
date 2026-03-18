<?php

namespace App\Services;

use App\Models\PrintActivationToken;
use App\Models\PrintApiKey;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class ActivationCodeService
{
    public function generate(
        PrintApiKey $apiKey,
        string $plainAgentKey,
        string $apiBaseUrl,
        string $tenantCode,
        ?int $agentId,
        int $ttlHours,
        bool $oneTime,
    ): array {
        $ttl = max(1, min(168, $ttlHours));
        $now = CarbonImmutable::now();
        $expiresAt = $now->addHours($ttl);
        $kid = $this->activeKid();
        $key = $this->resolveSecretKey($kid);
        $jti = (string) Str::uuid();

        $payload = [
            'v' => 1,
            'iat' => $now->timestamp,
            'exp' => $expiresAt->timestamp,
            'jti' => $jti,
            'one_time' => $oneTime,
            'tenant_code' => $tenantCode,
            'api_base_url' => rtrim($apiBaseUrl, '/'),
            'agent_id' => $agentId,
            'agent_key' => $plainAgentKey,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if (! is_string($json)) {
            throw new \RuntimeException('Unable to encode activation payload.');
        }

        $nonce = random_bytes(12);
        $aad = 'PSA1|'.$kid;
        $tag = '';
        $ciphertext = openssl_encrypt($json, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, $aad, 16);

        if (! is_string($ciphertext) || ! is_string($tag) || strlen($tag) !== 16) {
            throw new \RuntimeException('Unable to encrypt activation payload.');
        }

        $code = sprintf('PSA1.%s.%s', $kid, $this->base64UrlEncode($nonce.$ciphertext.$tag));

        PrintActivationToken::query()->create([
            'tenant_id' => $apiKey->tenant_id,
            'api_key_id' => $apiKey->id,
            'agent_id' => $apiKey->agent_id,
            'kid' => $kid,
            'jti_hash' => hash('sha256', $jti),
            'one_time' => $oneTime,
            'expires_at' => $expiresAt,
            'meta' => [
                'api_base_url' => rtrim($apiBaseUrl, '/'),
                'tenant_code' => $tenantCode,
            ],
        ]);

        return [
            'code' => $code,
            'expires_at' => $expiresAt,
            'kid' => $kid,
            'jti' => $jti,
            'one_time' => $oneTime,
        ];
    }

    public function keyInfo(): array
    {
        $ring = $this->resolveKeyring();

        return [
            'active_kid' => $this->activeKid(),
            'available_kids' => array_keys($ring),
        ];
    }

    public function decode(string $code): array
    {
        $parts = explode('.', trim($code), 3);
        if (count($parts) !== 3 || $parts[0] !== 'PSA1') {
            throw new \RuntimeException('Unsupported activation code format.');
        }

        $kid = $parts[1];
        $key = $this->resolveSecretKey($kid);
        $blob = $this->base64UrlDecode($parts[2]);

        if (strlen($blob) < 12 + 16) {
            throw new \RuntimeException('Activation code payload is too short.');
        }

        $nonce = substr($blob, 0, 12);
        $tag = substr($blob, -16);
        $ciphertext = substr($blob, 12, -16);
        $aad = 'PSA1|'.$kid;

        $json = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $nonce, $tag, $aad);
        if (! is_string($json) || $json === '') {
            throw new \RuntimeException('Activation code cannot be decrypted.');
        }

        $payload = json_decode($json, true);
        if (! is_array($payload)) {
            throw new \RuntimeException('Activation payload is invalid JSON.');
        }

        $exp = (int) ($payload['exp'] ?? 0);
        if ($exp <= now()->timestamp) {
            throw new \RuntimeException('Activation code is expired.');
        }

        $payload['kid'] = $kid;
        return $payload;
    }

    public function consumeOneTimeToken(array $payload, int $tenantId): void
    {
        $jti = (string) ($payload['jti'] ?? '');
        if ($jti === '') {
            throw new \RuntimeException('Activation token has no jti.');
        }

        $oneTime = (bool) ($payload['one_time'] ?? true);
        if (! $oneTime) {
            return;
        }

        $token = PrintActivationToken::query()
            ->where('tenant_id', $tenantId)
            ->where('jti_hash', hash('sha256', $jti))
            ->first();

        if (! $token) {
            throw new \RuntimeException('Activation token is not found.');
        }

        if ($token->used_at !== null) {
            throw new \RuntimeException('Activation token already used.');
        }

        if ($token->expires_at !== null && $token->expires_at->isPast()) {
            throw new \RuntimeException('Activation token expired.');
        }

        $token->update(['used_at' => now()]);
    }

    private function resolveSecretKey(string $kid): string
    {
        $ring = $this->resolveKeyring();
        $raw = $ring[$kid] ?? null;
        if ($raw === null) {
            throw new \RuntimeException("Activation key for kid [{$kid}] is not configured.");
        }

        if (strlen($raw) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \RuntimeException("Activation key for kid [{$kid}] must be 32 bytes.");
        }

        return $raw;
    }

    private function resolveKeyring(): array
    {
        $json = (string) config('activation.keyring_json', '');
        $result = [];

        if ($json !== '') {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $kid => $value) {
                    if (! is_string($kid) || ! is_string($value) || $kid === '') {
                        continue;
                    }

                    $result[$kid] = $this->decodeKeyValue($value);
                }
            }
        }

        if ($result === []) {
            $activeKid = $this->activeKid();
            $result[$activeKid] = $this->fallbackDerivedKey($activeKid);
        }

        return $result;
    }

    private function activeKid(): string
    {
        return trim((string) config('activation.active_kid', 'v1')) ?: 'v1';
    }

    private function decodeKeyValue(string $value): string
    {
        $raw = str_starts_with($value, 'base64:')
            ? base64_decode(substr($value, 7), true)
            : $value;

        if (! is_string($raw) || $raw === '') {
            throw new \RuntimeException('Invalid activation key value in keyring.');
        }

        if (strlen($raw) === SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            return $raw;
        }

        return hash('sha256', $raw, true);
    }

    private function fallbackDerivedKey(string $kid): string
    {
        $appKey = (string) config('app.key', '');
        if ($appKey === '') {
            throw new \RuntimeException('APP_KEY is not configured.');
        }

        $raw = str_starts_with($appKey, 'base64:')
            ? base64_decode(substr($appKey, 7), true)
            : $appKey;

        if (! is_string($raw) || $raw === '') {
            throw new \RuntimeException('Unable to derive activation key from APP_KEY.');
        }

        return hash('sha256', 'activation:'.$kid.'|'.$raw, true);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $padded = strtr($value, '-_', '+/');
        $mod = strlen($padded) % 4;
        if ($mod > 0) {
            $padded .= str_repeat('=', 4 - $mod);
        }

        $decoded = base64_decode($padded, true);
        if (! is_string($decoded)) {
            throw new \RuntimeException('Invalid activation code body.');
        }

        return $decoded;
    }
}
