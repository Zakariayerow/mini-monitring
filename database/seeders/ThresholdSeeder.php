<?php

namespace Database\Seeders;

use App\Models\Threshold;
use Illuminate\Database\Seeder;

class ThresholdSeeder extends Seeder
{
    public function run(): void
    {
        Threshold::updateOrCreate(
            ['metric' => 'cpu'],
            [
                'name' => 'CPU Usage',
                'warning' => 80,
                'critical' => 90,
                'operator' => '>',
                'enabled' => true,
                'description' => 'CPU usage threshold for monitored hosts.',
            ]
        );

        Threshold::updateOrCreate(
            ['metric' => 'memory'],
            [
                'name' => 'Memory Usage',
                'warning' => 80,
                'critical' => 90,
                'operator' => '>',
                'enabled' => true,
                'description' => 'Memory usage threshold for monitored hosts.',
            ]
        );

        Threshold::updateOrCreate(
            ['metric' => 'disk'],
            [
                'name' => 'Disk Usage',
                'warning' => 80,
                'critical' => 90,
                'operator' => '>',
                'enabled' => true,
                'description' => 'Disk usage threshold for monitored hosts.',
            ]
        );
    }
}