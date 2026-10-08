<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Metric;
use App\Models\Port;
use Illuminate\Support\Carbon;

class MonitoringService
{
    public function __construct(
        private SnmpService $snmp,
        private PingService $ping,
        private NetworkAlertService $networkAlerts
    ) {
    }

    public function collect(Device $device): array
    {
        $checkedAt = now();

        /*
        |--------------------------------------------------------------------------
        | 1. ICMP availability
        |--------------------------------------------------------------------------
        */

        if (!$this->ping->check($device->ip_address)) {
            $device->update([
                'status' => 'offline',
                'last_checked_at' => $checkedAt,
            ]);

            throw new \RuntimeException(
                'Device is not reachable by ping.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. SNMP availability
        |--------------------------------------------------------------------------
        */

        try {
            $systemDescription = $this->snmp->get(
                $device->ip_address,
                $device->snmp_community,
                '1.3.6.1.2.1.1.1.0',
                $device->snmp_port ?? 161
            );
        } catch (\Throwable $e) {
            $device->update([
                'status' => 'online',
                'last_checked_at' => $checkedAt,
            ]);

            throw new \RuntimeException(
                'Device is reachable but SNMP failed: '
                . $e->getMessage()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. System information
        |--------------------------------------------------------------------------
        */

        $systemMetrics = [];

        try {
            $uptime = $this->snmp->get(
                $device->ip_address,
                $device->snmp_community,
                '1.3.6.1.2.1.1.3.0',
                $device->snmp_port ?? 161
            );

            $systemMetrics['uptime'] =
                $this->parseSnmpNumber($uptime);
        } catch (\Throwable) {
            $systemMetrics['uptime'] = null;
        }

        try {
            $sysName = $this->snmp->get(
                $device->ip_address,
                $device->snmp_community,
                '1.3.6.1.2.1.1.5.0',
                $device->snmp_port ?? 161
            );

            $systemMetrics['sys_name'] =
                $this->cleanSnmpValue($sysName);
        } catch (\Throwable) {
            $systemMetrics['sys_name'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Interface discovery
        |--------------------------------------------------------------------------
        |
        | Standard IF-MIB:
        |
        | ifDescr       = 1.3.6.1.2.1.2.2.1.2
        | ifSpeed       = 1.3.6.1.2.1.2.2.1.5
        | ifAdminStatus = 1.3.6.1.2.1.2.2.1.7
        | ifOperStatus  = 1.3.6.1.2.1.2.2.1.8
        |
        */

        $ifNames = $this->snmp->walk(
            $device->ip_address,
            $device->snmp_community,
            '1.3.6.1.2.1.2.2.1.2',
            $device->snmp_port ?? 161
        );

        $ifStatuses = $this->snmp->walk(
            $device->ip_address,
            $device->snmp_community,
            '1.3.6.1.2.1.2.2.1.8',
            $device->snmp_port ?? 161
        );

        $ifAdminStatuses = $this->snmp->walk(
            $device->ip_address,
            $device->snmp_community,
            '1.3.6.1.2.1.2.2.1.7',
            $device->snmp_port ?? 161
        );

        $ifSpeeds = $this->snmp->walk(
            $device->ip_address,
            $device->snmp_community,
            '1.3.6.1.2.1.2.2.1.5',
            $device->snmp_port ?? 161
        );

        /*
        |--------------------------------------------------------------------------
        | 5. 64-bit traffic counters
        |--------------------------------------------------------------------------
        */

        $ifHCIn = $this->safeWalk(
            $device,
            '1.3.6.1.2.1.31.1.1.1.6'
        );

        $ifHCOut = $this->safeWalk(
            $device,
            '1.3.6.1.2.1.31.1.1.1.10'
        );

        /*
        |--------------------------------------------------------------------------
        | 6. 32-bit fallback counters
        |--------------------------------------------------------------------------
        */

        $ifIn = $this->safeWalk(
            $device,
            '1.3.6.1.2.1.2.2.1.10'
        );

        $ifOut = $this->safeWalk(
            $device,
            '1.3.6.1.2.1.2.2.1.16'
        );

        $ifPacketsIn = $this->safeWalk(
            $device,
            '1.3.6.1.2.1.2.2.1.11'
        );

        $ifPacketsOut = $this->safeWalk(
            $device,
            '1.3.6.1.2.1.2.2.1.17'
        );

        $ifErrorsIn = $this->safeWalk(
            $device,
            '1.3.6.1.2.1.2.2.1.14'
        );

        $ifErrorsOut = $this->safeWalk(
            $device,
            '1.3.6.1.2.1.2.2.1.20'
        );

        $interfaceCount = 0;

        /*
        |--------------------------------------------------------------------------
        | 7. Process interfaces
        |--------------------------------------------------------------------------
        */

        foreach ($ifNames as $oid => $nameValue) {
            $ifIndex = $this->extractIndex($oid);

            if ($ifIndex === null) {
                continue;
            }

            $name = $this->cleanSnmpValue($nameValue);

            $statusValue = $this->findWalkValue(
                $ifStatuses,
                $ifIndex
            );

            $adminStatusValue = $this->findWalkValue(
                $ifAdminStatuses,
                $ifIndex
            );

            $speedValue = $this->findWalkValue(
                $ifSpeeds,
                $ifIndex
            );

            /*
            |--------------------------------------------------------------------------
            | Traffic counters
            |--------------------------------------------------------------------------
            */

            $rxValue = $this->findWalkValue(
                $ifHCIn,
                $ifIndex
            );

            $txValue = $this->findWalkValue(
                $ifHCOut,
                $ifIndex
            );

            if ($rxValue === null) {
                $rxValue = $this->findWalkValue(
                    $ifIn,
                    $ifIndex
                );
            }

            if ($txValue === null) {
                $txValue = $this->findWalkValue(
                    $ifOut,
                    $ifIndex
                );
            }

            $packetsIn = $this->parseSnmpNumber(
                $this->findWalkValue(
                    $ifPacketsIn,
                    $ifIndex
                )
            );

            $packetsOut = $this->parseSnmpNumber(
                $this->findWalkValue(
                    $ifPacketsOut,
                    $ifIndex
                )
            );

            $errorsIn = $this->parseSnmpNumber(
                $this->findWalkValue(
                    $ifErrorsIn,
                    $ifIndex
                )
            );

            $errorsOut = $this->parseSnmpNumber(
                $this->findWalkValue(
                    $ifErrorsOut,
                    $ifIndex
                )
            );

            $rxBytes = $this->parseSnmpNumber(
                $rxValue
            );

            $txBytes = $this->parseSnmpNumber(
                $txValue
            );

            $speed = $this->parseSnmpNumber(
                $speedValue
            );

            /*
            |--------------------------------------------------------------------------
            | Operational status
            |--------------------------------------------------------------------------
            */

            $status = 'unknown';

            if ($statusValue !== null) {
                $statusNumber =
                    $this->parseSnmpNumber(
                        $statusValue
                    );

                if ($statusNumber !== null) {
                    $statusNumber = (int) $statusNumber;

                    $status = match ($statusNumber) {
                        1 => 'up',
                        2 => 'down',
                        3 => 'testing',
                        4 => 'unknown',
                        5 => 'dormant',
                        6 => 'notPresent',
                        7 => 'lowerLayerDown',
                        default => 'unknown',
                    };
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Administrative status
            |--------------------------------------------------------------------------
            */

            $adminStatus = 'unknown';

            if ($adminStatusValue !== null) {
                $adminStatusNumber =
                    $this->parseSnmpNumber(
                        $adminStatusValue
                    );

                if ($adminStatusNumber !== null) {
                    $adminStatusNumber =
                        (int) $adminStatusNumber;

                    $adminStatus = match (
                        $adminStatusNumber
                    ) {
                        1 => 'up',
                        2 => 'down',
                        3 => 'testing',
                        default => 'unknown',
                    };
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Existing port
            |--------------------------------------------------------------------------
            */

            $port = Port::where(
                'device_id',
                $device->id
            )
                ->where(
                    'if_index',
                    $ifIndex
                )
                ->first();

            $previousRx = $port?->rx_bytes;
            $previousTx = $port?->tx_bytes;
            $previousChecked =
                $port?->last_checked_at;

            /*
            |--------------------------------------------------------------------------
            | Traffic calculation
            |--------------------------------------------------------------------------
            */

            $traffic = $this->calculateTraffic(
                $previousRx,
                $previousTx,
                $previousChecked,
                $rxBytes,
                $txBytes,
                $speed,
                $checkedAt
            );

            /*
            |--------------------------------------------------------------------------
            | Save port
            |--------------------------------------------------------------------------
            */

            $port = Port::updateOrCreate(
                [
                    'device_id' => $device->id,
                    'if_index' => $ifIndex,
                ],
                [
                    'name' => $name,
                    'interface_name' => $name,
                    'status' => $status,
                    'admin_status' => $adminStatus,
                    'speed' => $speed,
                    'bytes_in' => $rxBytes,
                    'bytes_out' => $txBytes,
                    'packets_in' => $packetsIn,
                    'packets_out' => $packetsOut,
                    'errors_in' => $errorsIn,
                    'errors_out' => $errorsOut,
                    'rx_bytes' => $rxBytes,
                    'tx_bytes' => $txBytes,
                    'rx_bps' => $traffic['rx_bps'],
                    'tx_bps' => $traffic['tx_bps'],
                    'rx_utilization' =>
                        $traffic['rx_utilization'],
                    'tx_utilization' =>
                        $traffic['tx_utilization'],
                    'last_checked_at' => $checkedAt,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Network alerts
            |--------------------------------------------------------------------------
            */

            $this->networkAlerts->evaluatePort(
                $port
            );

            /*
            |--------------------------------------------------------------------------
            | Record historical interface metrics
            |--------------------------------------------------------------------------
            */

            $this->recordInterfaceMetrics(
                $device,
                $ifIndex,
                $traffic,
                $errorsIn,
                $errorsOut,
                $checkedAt
            );

            $interfaceCount++;
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Update device status
        |--------------------------------------------------------------------------
        */

        $device->update([
            'status' => 'online',
            'last_checked_at' => $checkedAt,
            'last_up_at' => $checkedAt,
        ]);

        return [
            'reachable' => true,
            'snmp' => true,
            'system_description' =>
                $this->cleanSnmpValue(
                    $systemDescription
                ),
            'system_metrics' => $systemMetrics,
            'interfaces' => $interfaceCount,
            'checked_at' => $checkedAt,
        ];
    }

    private function safeWalk(
        Device $device,
        string $oid
    ): array {
        try {
            return $this->snmp->walk(
                $device->ip_address,
                $device->snmp_community,
                $oid,
                $device->snmp_port ?? 161
            );
        } catch (\Throwable) {
            return [];
        }
    }

    private function calculateTraffic(
        mixed $previousRx,
        mixed $previousTx,
        ?Carbon $previousChecked,
        mixed $currentRx,
        mixed $currentTx,
        mixed $speed,
        Carbon $currentChecked
    ): array {
        $result = [
            'rx_bps' => null,
            'tx_bps' => null,
            'rx_utilization' => null,
            'tx_utilization' => null,
        ];

        if (
            $previousChecked === null ||
            $previousRx === null ||
            $previousTx === null ||
            $currentRx === null ||
            $currentTx === null
        ) {
            return $result;
        }

        $elapsed = $previousChecked->diffInSeconds(
            $currentChecked
        );

        if ($elapsed <= 0) {
            return $result;
        }

        $rxDelta = $this->counterDelta(
            (float) $previousRx,
            (float) $currentRx
        );

        $txDelta = $this->counterDelta(
            (float) $previousTx,
            (float) $currentTx
        );

        if (
            $rxDelta === null ||
            $txDelta === null
        ) {
            return $result;
        }

        $rxBps = ($rxDelta * 8) / $elapsed;
        $txBps = ($txDelta * 8) / $elapsed;

        $result['rx_bps'] = $rxBps;
        $result['tx_bps'] = $txBps;

        $speed = (float) $speed;

        if ($speed > 0) {
            $result['rx_utilization'] = min(
                100,
                max(
                    0,
                    ($rxBps / $speed) * 100
                )
            );

            $result['tx_utilization'] = min(
                100,
                max(
                    0,
                    ($txBps / $speed) * 100
                )
            );
        }

        return $result;
    }

    private function counterDelta(
        float $previous,
        float $current
    ): ?float {
        if ($current >= $previous) {
            return $current - $previous;
        }

        return null;
    }

    private function recordInterfaceMetrics(
        Device $device,
        int $ifIndex,
        array $traffic,
        ?float $errorsIn,
        ?float $errorsOut,
        Carbon $recordedAt
    ): void {
        $metrics = [
            "interface.{$ifIndex}.rx_bps" => [
                'value' => $traffic['rx_bps'],
                'unit' => 'bps',
            ],
            "interface.{$ifIndex}.tx_bps" => [
                'value' => $traffic['tx_bps'],
                'unit' => 'bps',
            ],
            "interface.{$ifIndex}.rx_utilization" => [
                'value' =>
                    $traffic['rx_utilization'],
                'unit' => 'percent',
            ],
            "interface.{$ifIndex}.tx_utilization" => [
                'value' =>
                    $traffic['tx_utilization'],
                'unit' => 'percent',
            ],
            "interface.{$ifIndex}.errors_in" => [
                'value' => $errorsIn,
                'unit' => 'count',
            ],
            "interface.{$ifIndex}.errors_out" => [
                'value' => $errorsOut,
                'unit' => 'count',
            ],
        ];

        foreach ($metrics as $metric => $data) {
            if ($data['value'] === null) {
                continue;
            }

            Metric::create([
                'device_id' => $device->id,
                'metric' => $metric,
                'value' => $data['value'],
                'unit' => $data['unit'],
                'recorded_at' => $recordedAt,
            ]);
        }
    }

    private function extractIndex(
        string $oid
    ): ?int {
        $parts = explode('.', $oid);

        $last = end($parts);

        return is_numeric($last)
            ? (int) $last
            : null;
    }

    private function findWalkValue(
        array $walk,
        int $ifIndex
    ): mixed {
        foreach ($walk as $oid => $value) {
            if (
                $this->extractIndex($oid)
                === $ifIndex
            ) {
                return $value;
            }
        }

        return null;
    }

    private function parseSnmpNumber(
        mixed $value
    ): ?float {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (
            preg_match(
                '/^[A-Za-z0-9-]+:\s*(-?\d+(?:\.\d+)?)/',
                $value,
                $matches
            )
        ) {
            return (float) $matches[1];
        }

        if (
            preg_match(
                '/^\((\d+)\)/',
                $value,
                $matches
            )
        ) {
            return (float) $matches[1];
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function cleanSnmpValue(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        $value = preg_replace(
            '/^(STRING|OID|Hex-STRING):\s*/',
            '',
            $value
        );

        return trim(
            $value,
            "\" \t\n\r\0\x0B"
        );
    }
}
