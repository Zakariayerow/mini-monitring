<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Host extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hostname',
        'ip_address',
        'agent_key',
        'operating_system',
        'architecture',
        'status',
        'cpu_percent',
        'memory_percent',
        'disk_percent',
        'memory_total',
        'memory_used',
        'disk_total',
        'disk_used',
        'uptime',
        'last_seen_at',
        'description',
    ];

    protected $casts = [
        'cpu_percent' => 'float',
        'memory_percent' => 'float',
        'disk_percent' => 'float',
        'memory_total' => 'float',
        'memory_used' => 'float',
        'disk_total' => 'float',
        'disk_used' => 'float',
        'last_seen_at' => 'datetime',
    ];

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }
}