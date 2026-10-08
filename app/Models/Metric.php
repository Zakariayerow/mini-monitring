<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metric extends Model
{
    use HasFactory;
    protected $fillable = [
        'host_id',
        'device_id',
        'metric',
        'value',
        'unit',
        'recorded_at',
    ];

    protected $casts = [
        'host_id' => 'integer',
        'device_id' => 'integer',
        'value' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }
}