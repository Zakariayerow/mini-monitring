<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . '-Device-' . $this->faker->randomNumber(2),
            'hostname' => $this->faker->domainName(),
            'ip_address' => $this->faker->ipv4(),
            'type' => $this->faker->randomElement(['Router', 'Switch', 'Firewall', 'Access Point', 'Server']),
            'vendor' => $this->faker->randomElement(['Cisco', 'Arista', 'Huawei', 'HPE', 'Juniper']),
            'model' => $this->faker->word(),
            'operating_system' => $this->faker->word(),
            'status' => 'online',
            'snmp_port' => 161,
            'snmp_version' => '2c',
            'snmp_community' => 'public',
            'description' => $this->faker->sentence(),
        ];
    }
}
