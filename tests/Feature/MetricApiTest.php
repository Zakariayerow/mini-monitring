<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Host;
use App\Models\Metric;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_metric_store_and_list_endpoints_work(): void
    {
        $host = Host::create([
            'name' => 'API Host',
            'hostname' => 'api-host',
            'ip_address' => '192.168.1.25',
            'agent_key' => 'minimon-api-metrics',
            'status' => 'online',
        ]);

        $response = $this->postJson('/api/metrics', [
            'host_id' => $host->id,
            'metric' => 'cpu',
            'value' => 55.4,
            'unit' => 'percent',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('metrics', [
            'host_id' => $host->id,
            'metric' => 'cpu',
            'value' => '55.4000',
        ]);

        $list = $this->getJson('/api/metrics?host_id=' . $host->id);
        $list->assertOk();
        $list->assertJsonPath('success', true);
    }

    public function test_metric_store_requires_either_host_or_device(): void
    {
        $response = $this->postJson('/api/metrics', [
            'metric' => 'cpu',
            'value' => 12.5,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_host_and_device_metric_routes_return_data(): void
    {
        $host = Host::create([
            'name' => 'Host Metrics',
            'hostname' => 'host-metrics',
            'ip_address' => '192.168.1.75',
            'agent_key' => 'minimon-metric-host',
            'status' => 'online',
        ]);

        $device = Device::create([
            'name' => 'Core Switch',
            'type' => 'Switch',
            'ip_address' => '192.168.1.10',
            'status' => 'online',
        ]);

        Metric::create([
            'host_id' => $host->id,
            'metric' => 'cpu',
            'value' => 42,
            'unit' => 'percent',
            'recorded_at' => now(),
        ]);

        Metric::create([
            'device_id' => $device->id,
            'metric' => 'interface_errors',
            'value' => 7,
            'unit' => 'count',
            'recorded_at' => now(),
        ]);

        $this->getJson('/api/hosts/' . $host->id . '/metrics')->assertOk();
        $this->getJson('/api/devices/' . $device->id . '/metrics')->assertOk();
    }
}
