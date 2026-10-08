<?php

namespace Tests\Unit;

use App\Models\Alert;
use App\Models\Host;
use App\Models\Threshold;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_thresholds_are_available_for_host_metrics(): void
    {
        $this->seed();

        $this->assertDatabaseHas('thresholds', [
            'metric' => 'cpu',
            'warning' => 80,
            'critical' => 90,
            'enabled' => true,
        ]);

        $this->assertDatabaseHas('thresholds', [
            'metric' => 'memory',
            'warning' => 80,
            'critical' => 90,
            'enabled' => true,
        ]);

        $this->assertDatabaseHas('thresholds', [
            'metric' => 'disk',
            'warning' => 80,
            'critical' => 90,
            'enabled' => true,
        ]);
    }

    public function test_threshold_alerts_are_created_and_resolved(): void
    {
        Threshold::create([
            'name' => 'CPU Usage',
            'metric' => 'cpu',
            'warning' => 80,
            'critical' => 90,
            'operator' => '>',
            'enabled' => true,
            'description' => 'CPU usage threshold for monitored hosts.',
        ]);

        $service = new AlertService();
        $hostId = 42;

        $status = $service->evaluate('cpu', 85, Host::class, $hostId);

        $this->assertSame('warning', $status);
        $this->assertDatabaseHas('alerts', [
            'metric' => 'cpu',
            'severity' => 'warning',
            'alertable_type' => Host::class,
            'alertable_id' => $hostId,
            'resolved_at' => null,
        ]);

        $status = $service->evaluate('cpu', 45, Host::class, $hostId);

        $this->assertSame('normal', $status);

        $alert = Alert::where('metric', 'cpu')
            ->where('alertable_type', Host::class)
            ->where('alertable_id', $hostId)
            ->firstOrFail();

        $this->assertNotNull($alert->resolved_at);
    }
}
