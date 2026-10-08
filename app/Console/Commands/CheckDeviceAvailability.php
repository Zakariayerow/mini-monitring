<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\PingService;
use Illuminate\Console\Command;

class CheckDeviceAvailability extends Command
{
    protected $signature = 'minimon:ping';

    protected $description =
        'Check availability of all registered devices';

    public function handle(
        PingService $ping
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
                "Pinging {$device->name} "
                . "({$device->ip_address})..."
            );

            $checkedAt = now();

            try {
                $online = $ping->check(
                    $device->ip_address
                );

                if ($online) {
                    $device->update([
                        'status' => 'online',
                        'last_checked_at' => $checkedAt,
                        'last_up_at' => $checkedAt,
                    ]);

                    $this->info(
                        "ONLINE: {$device->name}"
                    );
                } else {
                    $device->update([
                        'status' => 'offline',
                        'last_checked_at' => $checkedAt,
                    ]);

                    $this->error(
                        "OFFLINE: {$device->name}"
                    );
                }
            } catch (\Throwable $e) {
                $device->update([
                    'status' => 'offline',
                    'last_checked_at' => $checkedAt,
                ]);

                $this->error(
                    "ERROR: {$device->name} - "
                    . $e->getMessage()
                );
            }
        }

        return self::SUCCESS;
    }
}