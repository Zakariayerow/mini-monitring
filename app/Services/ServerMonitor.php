<?php

namespace App\Services;

use App\Models\Host;
use App\Models\Metric;
use Illuminate\Support\Facades\Log;

class ServerMonitor
{
    public function __construct(
        private AlertService $alerts
    ) {
    }

    public function updateHost(
        Host $host,
        array $data
    ): Host {
        $now = now();

        $host->update([
            'status' => 'online',
            'cpu_percent' => $data['cpu_percent'] ?? null,
            'memory_percent' => $data['memory_percent'] ?? null,
            'disk_percent' => $data['disk_percent'] ?? null,
            'memory_total' => $data['memory_total'] ?? null,
            'memory_used' => $data['memory_used'] ?? null,
            'disk_total' => $data['disk_total'] ?? null,
            'disk_used' => $data['disk_used'] ?? null,
            'uptime' => $data['uptime'] ?? null,
            'last_seen_at' => $now,
        ]);

        $this->recordMetrics(
            $host,
            $data,
            $now
        );

        $this->evaluateAlerts(
            $host,
            $data
        );

        return $host->fresh();
    }

    private function recordMetrics(
        Host $host,
        array $data,
        $recordedAt
    ): void {
        $metrics = [
            'cpu' => [
                'value' => $data['cpu_percent'] ?? null,
                'unit' => 'percent',
            ],

            'memory' => [
                'value' => $data['memory_percent'] ?? null,
                'unit' => 'percent',
            ],

            'disk' => [
                'value' => $data['disk_percent'] ?? null,
                'unit' => 'percent',
            ],

            'memory.used' => [
                'value' => $data['memory_used'] ?? null,
                'unit' => 'bytes',
            ],

            'memory.total' => [
                'value' => $data['memory_total'] ?? null,
                'unit' => 'bytes',
            ],

            'disk.used' => [
                'value' => $data['disk_used'] ?? null,
                'unit' => 'bytes',
            ],

            'disk.total' => [
                'value' => $data['disk_total'] ?? null,
                'unit' => 'bytes',
            ],
        ];

        foreach ($metrics as $metric => $information) {
            if ($information['value'] === null) {
                continue;
            }

            Metric::create([
                'host_id' => $host->id,
                'metric' => $metric,
                'value' => $information['value'],
                'unit' => $information['unit'],
                'recorded_at' => $recordedAt,
            ]);
        }
    }

    private function evaluateAlerts(
        Host $host,
        array $data
    ): void {
        $metrics = [
            'cpu' => $data['cpu_percent'] ?? null,
            'memory' => $data['memory_percent'] ?? null,
            'disk' => $data['disk_percent'] ?? null,
        ];

        foreach ($metrics as $metric => $value) {
            if ($value === null) {
                continue;
            }

            try {
                $this->alerts->evaluate(
                    $metric,
                    (float) $value,
                    Host::class,
                    $host->id
                );
            } catch (\Throwable $e) {
                Log::error(
                    'MiniMon alert evaluation failed.',
                    [
                        'host_id' => $host->id,
                        'metric' => $metric,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }
    }

    public function calculateHealth(
        ?float $cpu,
        ?float $memory,
        ?float $disk
    ): string {
        if (
            $cpu === null &&
            $memory === null &&
            $disk === null
        ) {
            return 'unknown';
        }

        if (
            ($cpu !== null && $cpu >= 90) ||
            ($memory !== null && $memory >= 90) ||
            ($disk !== null && $disk >= 90)
        ) {
            return 'critical';
        }

        if (
            ($cpu !== null && $cpu >= 80) ||
            ($memory !== null && $memory >= 80) ||
            ($disk !== null && $disk >= 80)
        ) {
            return 'warning';
        }

        return 'healthy';
    }
}