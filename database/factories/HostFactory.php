<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . '-' . $this->faker->randomNumber(2),
            'hostname' => $this->faker->domainName(),
            'ip_address' => $this->faker->ipv4(),
            'operating_system' => $this->faker->randomElement(['Ubuntu 22.04', 'CentOS 8', 'Windows Server 2022', 'RHEL 9']),
            'architecture' => 'x86_64',
            'agent_key' => Str::random(32),
            'status' => 'online',
            'description' => $this->faker->sentence(),
        ];
    }
}
