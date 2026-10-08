<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hostname',
        'type',
        'floor',
        'ip_address',
        'vendor',
        'model',
        'operating_system',
        'status',
        'snmp_port',
        'snmp_version',
        'snmp_community',
        'last_checked_at',
        'last_up_at',
        'description',
    ];

    protected $casts = [
        'snmp_port' => 'integer',
        'last_checked_at' => 'datetime',
        'last_up_at' => 'datetime',
    ];

    public function ports(): HasMany
    {
        return $this->hasMany(Port::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class);
    }
}