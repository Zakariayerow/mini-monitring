<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\MonitoringService;
use Illuminate\Console\Command;

class MonitorDevices extends Command
{
    protected $signature = 'minimon:monitor';

    protected $description =
        'Collect SNMP monitoring data from all devices';

    public function handle(
        MonitoringService $monitoring
    ): int {
        $devices = Device::all();

        if ($devices->isEmpty()) {
            $this->info(
                'No devices registered.'
            );

            return self::SUCCESS;
        }

        foreach ($devices as $device) {
            $this->line(
                "Checking {$device->name} "
                . "({$device->ip_address})..."
            );

            try {
                $result =
                    $monitoring->collect($device);

                $this->info(
                    "ONLINE/SNMP OK: "
                    . "{$device->name} - "
                    . $result['interfaces']
                    . " interfaces"
                );
            } catch (\Throwable $e) {
                $this->error(
                    "MONITORING FAILED: "
                    . "{$device->name} - "
                    . $e->getMessage()
                );
            }
        }

        return self::SUCCESS;
    }
}