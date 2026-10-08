<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Host;
use App\Models\Metric;
use App\Models\Port;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    public function generateHostReport(Host $host, Carbon $periodStart, Carbon $periodEnd): Report
    {
        $metrics = Metric::where('host_id', $host->id)
            ->whereBetween('recorded_at', [$periodStart, $periodEnd])
            ->select('metric', 'value', 'recorded_at')
            ->get()
            ->groupBy('metric')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'avg' => $group->avg('value'),
                    'min' => $group->min('value'),
                    'max' => $group->max('value'),
                ];
            });

        $report = Report::create([
            'name' => "Host Report: {$host->name} ({$periodStart->format('Y-m-d')} to {$periodEnd->format('Y-m-d')})",
            'type' => 'host',
            'host_id' => $host->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'metrics' => $metrics->toArray(),
            'status' => 'completed',
        ]);

        return $report;
    }

    public function generateDeviceReport(Device $device, Carbon $periodStart, Carbon $periodEnd): Report
    {
        $portData = [];

        foreach ($device->ports as $port) {
            $alerts = $port->alerts()
                ->whereBetween('triggered_at', [$periodStart, $periodEnd])
                ->count();

            $portData[$port->name] = [
                'status' => $port->status,
                'alerts' => $alerts,
                'last_checked' => $port->updated_at?->toDateTimeString(),
            ];
        }

        $report = Report::create([
            'name' => "Device Report: {$device->name} ({$periodStart->format('Y-m-d')} to {$periodEnd->format('Y-m-d')})",
            'type' => 'device',
            'device_id' => $device->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'metrics' => $portData ?: ['message' => 'No port data available'],
            'status' => 'completed',
        ]);

        return $report;
    }

    public function generateSummaryReport(Carbon $periodStart, Carbon $periodEnd): Report
    {
        $hostCount = Host::count();
        $deviceCount = Device::count();
        $hostOnline = Host::where('status', 'online')->count();
        $deviceHealthy = Device::where('status', 'healthy')->count();

        $metrics = [
            'hosts' => [
                'total' => $hostCount,
                'online' => $hostOnline,
                'offline' => $hostCount - $hostOnline,
            ],
            'devices' => [
                'total' => $deviceCount,
                'healthy' => $deviceHealthy,
                'unhealthy' => $deviceCount - $deviceHealthy,
            ],
            'period' => [
                'start' => $periodStart->toDateTimeString(),
                'end' => $periodEnd->toDateTimeString(),
            ],
        ];

        $report = Report::create([
            'name' => "Infrastructure Summary ({$periodStart->format('Y-m-d')} to {$periodEnd->format('Y-m-d')})",
            'type' => 'summary',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'metrics' => $metrics,
            'status' => 'completed',
        ]);

        return $report;
    }

    public function exportReportAsCsv(Report $report): string
    {
        $filename = "report_{$report->id}_{$report->period_start->format('Y-m-d')}.csv";
        $path = "reports/{$filename}";

        $csv = "Report: {$report->name}\n";
        $csv .= "Type: {$report->type}\n";
        $csv .= "Period: {$report->period_start->format('Y-m-d H:i')} to {$report->period_end->format('Y-m-d H:i')}\n\n";

        if ($report->metrics) {
            $metrics = $report->metrics;
            if ($report->type === 'host') {
                $csv .= "Metric,Count,Average,Minimum,Maximum\n";
                foreach ($metrics as $metric => $data) {
                    $csv .= "{$metric}," . implode(',', $data) . "\n";
                }
            } elseif ($report->type === 'device') {
                $csv .= "Port,Status,Alerts,Last Checked\n";
                foreach ($metrics as $port => $data) {
                    $csv .= "\"{$port}\"," . implode(',', $data) . "\n";
                }
            } elseif ($report->type === 'summary') {
                $csv .= "Category,Value\n";
                $this->flattenArray($metrics, $csv);
            }
        }

        Storage::disk('local')->put($path, $csv);
        $report->update(['file_path' => $path, 'status' => 'exported']);

        return $path;
    }

    private function flattenArray(array $array, string &$csv, string $prefix = ''): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->flattenArray($value, $csv, "{$prefix}{$key}/");
            } else {
                $csv .= "{$prefix}{$key},{$value}\n";
            }
        }
    }

    public function deleteOldReports(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);

        $reports = Report::where('created_at', '<', $cutoffDate)->get();

        foreach ($reports as $report) {
            if ($report->file_path && Storage::disk('local')->exists($report->file_path)) {
                Storage::disk('local')->delete($report->file_path);
            }
            $report->delete();
        }

        return $reports->count();
    }
}
