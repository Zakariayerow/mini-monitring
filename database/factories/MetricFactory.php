<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MetricFactory extends Factory
{
    public function definition(): array
    {
        return [
            'host_id' => null,
            'device_id' => null,
            'metric' => $this->faker->randomElement(['cpu', 'memory', 'disk', 'uptime', 'load_average', 'network_in', 'network_out']),
            'value' => $this->faker->randomFloat(2, 0, 100),
            'unit' => $this->faker->randomElement(['%', 'MB', 'GB', 'Mbps', 'ms']),
            'recorded_at' => now(),
        ];
    }
}
