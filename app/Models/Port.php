<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Port extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'interface_name',
        'if_index',
        'status',
        'admin_status',
        'speed',
        'bytes_in',
        'bytes_out',
        'packets_in',
        'packets_out',
        'errors_in',
        'errors_out',
        'rx_bytes',
        'tx_bytes',
        'rx_bps',
        'tx_bps',
        'rx_utilization',
        'tx_utilization',
        'last_checked_at',
    ];

    protected $casts = [
        'device_id' => 'integer',
        'if_index' => 'integer',
        'speed' => 'integer',
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
        'packets_in' => 'integer',
        'packets_out' => 'integer',
        'errors_in' => 'integer',
        'errors_out' => 'integer',
        'rx_bytes' => 'integer',
        'tx_bytes' => 'integer',
        'rx_bps' => 'float',
        'tx_bps' => 'float',
        'rx_utilization' => 'float',
        'tx_utilization' => 'float',
        'last_checked_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
