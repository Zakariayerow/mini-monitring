<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Host;
use App\Models\Metric;
use App\Models\Report;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private ReportService $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportService = new ReportService();
    }

    public function test_can_generate_summary_report(): void
    {
        Host::factory()->count(5)->create(['status' => 'online']);
        Host::factory()->count(2)->create(['status' => 'offline']);
        Device::factory()->count(3)->create(['status' => 'healthy']);

        $periodStart = now()->subDays(7);
        $periodEnd = now();

        $report = $this->reportService->generateSummaryReport($periodStart, $periodEnd);

        $this->assertNotNull($report);
        $this->assertEquals('summary', $report->type);
        $this->assertEquals('completed', $report->status);
        $this->assertEquals(7, $report->metrics['hosts']['total']);
        $this->assertEquals(5, $report->metrics['hosts']['online']);
        $this->assertEquals(3, $report->metrics['devices']['total']);
    }

    public function test_can_generate_host_report(): void
    {
        $host = Host::factory()->create();
        Metric::factory()->count(10)->create([
            'host_id' => $host->id,
            'metric' => 'cpu',
            'value' => 45,
        ]);
        Metric::factory()->count(10)->create([
            'host_id' => $host->id,
            'metric' => 'memory',
            'value' => 60,
        ]);

        $periodStart = now()->subDays(7);
        $periodEnd = now();

        $report = $this->reportService->generateHostReport($host, $periodStart, $periodEnd);

        $this->assertNotNull($report);
        $this->assertEquals('host', $report->type);
        $this->assertEquals($host->id, $report->host_id);
        $this->assertArrayHasKey('cpu', $report->metrics);
        $this->assertArrayHasKey('memory', $report->metrics);
    }

    public function test_can_generate_device_report(): void
    {
        $device = Device::factory()->create();

        $periodStart = now()->subDays(7);
        $periodEnd = now();

        $report = $this->reportService->generateDeviceReport($device, $periodStart, $periodEnd);

        $this->assertNotNull($report);
        $this->assertEquals('device', $report->type);
        $this->assertEquals($device->id, $report->device_id);
    }

    public function test_can_export_report_as_csv(): void
    {
        $report = Report::factory()->create([
            'type' => 'summary',
            'metrics' => [
                'hosts' => ['total' => 5, 'online' => 4],
                'devices' => ['total' => 3],
            ],
        ]);

        $path = $this->reportService->exportReportAsCsv($report);

        $this->assertNotNull($path);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('local')->exists($path));

        $report->refresh();
        $this->assertEquals('exported', $report->status);
        $this->assertEquals($path, $report->file_path);
    }

    public function test_can_list_reports(): void
    {
        Report::factory()->count(5)->create();

        $response = $this->get(route('reports.index'));

        $response->assertStatus(200);
        $response->assertViewHas('reports');
    }

    public function test_can_create_report_from_form(): void
    {
        $host = Host::factory()->create();

        $response = $this->post(route('reports.store'), [
            'type' => 'host',
            'host_id' => $host->id,
            'period_days' => 30,
        ]);

        $this->assertDatabaseHas('reports', [
            'type' => 'host',
            'host_id' => $host->id,
        ]);

        $response->assertRedirect();
    }

    public function test_can_view_report(): void
    {
        $report = Report::factory()->create();

        $response = $this->get(route('reports.show', $report));

        $response->assertStatus(200);
        $response->assertViewHas('report');
    }

    public function test_can_delete_report(): void
    {
        $report = Report::factory()->create();

        $response = $this->delete(route('reports.destroy', $report));

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
        $response->assertRedirect(route('reports.index'));
    }
}
