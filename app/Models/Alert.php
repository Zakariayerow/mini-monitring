<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'alertable_type',
        'alertable_id',
        'type',
        'severity',
        'metric',
        'value',
        'threshold',
        'message',
        'triggered_at',
        'resolved_at',
    ];

    protected $casts = [
        'value' => 'float',
        'threshold' => 'float',
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
}