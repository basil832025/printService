<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PrintAgent extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'machine_uid',
        'status',
        'os_info',
        'version',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(PrintTenant::class, 'tenant_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(PrintJob::class, 'reserved_by_agent_id');
    }
}
