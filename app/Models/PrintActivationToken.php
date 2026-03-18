<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintActivationToken extends Model
{
    protected $fillable = [
        'tenant_id',
        'api_key_id',
        'agent_id',
        'kid',
        'jti_hash',
        'one_time',
        'expires_at',
        'used_at',
        'meta',
    ];

    protected $casts = [
        'one_time' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PrintTenant::class, 'tenant_id');
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(PrintApiKey::class, 'api_key_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(PrintAgent::class, 'agent_id');
    }
}
