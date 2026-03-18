<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PrintJob extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'source_system',
        'printer_selector',
        'job_type',
        'content_type',
        'payload',
        'copies',
        'priority',
        'status',
        'idempotency_key',
        'reserved_by_agent_id',
        'reserved_until',
        'attempts_count',
        'next_retry_at',
        'error_code',
        'error_message',
        'printed_at',
    ];

    protected $casts = [
        'reserved_until' => 'datetime',
        'next_retry_at' => 'datetime',
        'printed_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PrintTenant::class, 'tenant_id');
    }

    public function reservedByAgent(): BelongsTo
    {
        return $this->belongsTo(PrintAgent::class, 'reserved_by_agent_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PrintJobAttempt::class, 'job_id');
    }
}
