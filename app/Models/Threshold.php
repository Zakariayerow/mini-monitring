<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Threshold extends Model
{
    protected $fillable = [
        'name',
        'metric',
        'warning',
        'critical',
        'operator',
        'enabled',
        'description',
    ];

    protected $casts = [
        'warning' => 'float',
        'critical' => 'float',
        'enabled' => 'boolean',
    ];
}
