<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['summary', 'host', 'device']),
            'period_start' => now()->subDays(7),
            'period_end' => now(),
            'metrics' => [],
            'status' => 'completed',
        ];
    }
}
