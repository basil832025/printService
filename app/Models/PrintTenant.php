<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PrintTenant extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function agents(): HasMany
    {
        return $this->hasMany(PrintAgent::class, 'tenant_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(PrintJob::class, 'tenant_id');
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(PrintApiKey::class, 'tenant_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }
}
