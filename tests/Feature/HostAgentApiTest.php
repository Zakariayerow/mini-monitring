<?php

namespace Tests\Feature;

use App\Models\Host;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostAgentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_report_accepts_valid_agent_key(): void
    {
        $host = Host::create([
            'name' => 'Web Server',
            'hostname' => 'web-01',
            'ip_address' => '192.168.1.20',
            'agent_key' => 'minimon-test-key',
            'status' => 'unknown',
        ]);

        $response = $this->postJson('/api/agent/report', [
            'agent_key' => 'minimon-test-key',
            'hostname' => 'web-01',
            'operating_system' => 'Linux 6.8',
            'architecture' => 'x64',
            'cpu_percent' => 42.5,
            'memory_percent' => 68.1,
            'memory_total' => 16106127360,
            'memory_used' => 10995116277,
            'disk_percent' => 55.2,
            'disk_total' => 214748364800,
            'disk_used' => 118111600000,
            'uptime' => 123456,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertSame('web-01', $host->fresh()->hostname);
        $this->assertSame('online', $host->fresh()->status);
    }

    public function test_agent_report_rejects_invalid_agent_key(): void
    {
        $response = $this->postJson('/api/agent/report', [
            'agent_key' => 'missing-key',
            'cpu_percent' => 10,
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }

    public function test_agent_accepts_bearer_token_auth(): void
    {
        $host = Host::create([
            'name' => 'DB Server',
            'hostname' => 'db-01',
            'ip_address' => '192.168.1.30',
            'agent_key' => 'minimon-bearer-key',
            'status' => 'unknown',
        ]);

        $response = $this->withHeader(
            'Authorization',
            'Bearer minimon-bearer-key'
        )->postJson('/api/agent/metrics', [
            'cpu_percent' => 71,
            'memory_percent' => 74,
            'disk_percent' => 60,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertSame('online', $host->fresh()->status);
    }
}
