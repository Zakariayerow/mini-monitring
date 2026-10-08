<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Metric;
use App\Models\Port;
use Illuminate\Support\Carbon;

class NetworkAlertService
{
    public function evaluatePort(Port $port): void
    {
        $this->evaluateStatus($port);
        $this->evaluateUtilization($port);
        $this->evaluateErrors($port);
    }

    private function evaluateStatus(Port $port): void
    {
        /*
         * If an administrator intentionally disabled
         * the interface, do not generate a critical
         * interface-down alert.
         */
        if ($port->admin_status === 'down') {
            $this->resolveAlert(
                $port,
                'interface_status'
            );

            return;
        }

        /*
         * Only evaluate operational failure when the
         * administrative state allows the interface
         * to operate.
         */
        if (
            $port->admin_status === 'up' &&
            in_array(
                $port->status,
                [
                    'down',
                    'lowerLayerDown',
                ],
                true
            )
        ) {
            $this->createOrUpdateAlert(
                $port,
                'interface_status',
                'critical',
                'interface_status',
                1,
                "Interface {$port->name} is {$port->status}."
            );

            return;
        }

        /*
         * Unknown administrative state:
         * avoid creating a false critical alert.
         */
        $this->resolveAlert(
            $port,
            'interface_status'
        );
    }

    private function evaluateUtilization(Port $port): void
    {
        $this->evaluateUtilizationMetric(
            $port,
            'rx_utilization',
            $port->rx_utilization,
            'RX'
        );

        $this->evaluateUtilizationMetric(
            $port,
            'tx_utilization',
            $port->tx_utilization,
            'TX'
        );
    }

    private function evaluateUtilizationMetric(
        Port $port,
        string $metric,
        ?float $value,
        string $direction
    ): void {
        if ($value === null) {
            return;
        }

        if ($value >= 90) {
            $this->createOrUpdateAlert(
                $port,
                'utilization',
                'critical',
                $metric,
                $value,
                "Interface {$port->name} {$direction} utilization is {$value}%."
            );

            return;
        }

        if ($value >= 80) {
            $this->createOrUpdateAlert(
                $port,
                'utilization',
                'warning',
                $metric,
                $value,
                "Interface {$port->name} {$direction} utilization is {$value}%."
            );

            return;
        }

        $this->resolveAlert(
            $port,
            $metric
        );
    }

    private function evaluateErrors(Port $port): void
    {
        /*
         * SNMP interface error counters are cumulative.
         *
         * Example:
         *
         * Previous: 100
         * Current:  105
         *
         * New errors = 5
         *
         * We therefore compare the current Port counter
         * against the previous historical Metric value.
         */

        $previousErrorsIn =
            $this->getPreviousMetricValue(
                $port,
                "interface.{$port->if_index}.errors_in"
            );

        $previousErrorsOut =
            $this->getPreviousMetricValue(
                $port,
                "interface.{$port->if_index}.errors_out"
            );

        /*
         * We need a previous sample before calculating
         * a delta.
         */
        if (
            $previousErrorsIn === null &&
            $previousErrorsOut === null
        ) {
            return;
        }

        $currentErrorsIn =
            (int) ($port->errors_in ?? 0);

        $currentErrorsOut =
            (int) ($port->errors_out ?? 0);

        $previousErrorsIn =
            (int) ($previousErrorsIn ?? 0);

        $previousErrorsOut =
            (int) ($previousErrorsOut ?? 0);

        /*
         * Calculate newly observed errors.
         */
        $newErrorsIn =
            $this->counterDelta(
                $previousErrorsIn,
                $currentErrorsIn
            );

        $newErrorsOut =
            $this->counterDelta(
                $previousErrorsOut,
                $currentErrorsOut
            );

        $newErrors =
            $newErrorsIn + $newErrorsOut;

        /*
         * No new errors during this monitoring interval.
         */
        if ($newErrors <= 0) {
            $this->resolveAlert(
                $port,
                'interface_errors'
            );

            return;
        }

        /*
         * New interface errors detected.
         */
        $this->createOrUpdateAlert(
            $port,
            'interface_errors',
            'warning',
            'interface_errors',
            $newErrors,
            "Interface {$port->name} recorded {$newErrors} new interface errors."
        );
    }

    private function getPreviousMetricValue(
        Port $port,
        string $metricName
    ): ?float {
        $query = Metric::where(
            'device_id',
            $port->device_id
        )
            ->where(
                'metric',
                $metricName
            );

        /*
         * The MonitoringService evaluates the alert before
         * recording the current metric. Therefore, find
         * the most recent historical sample before the
         * current port check.
         */
        if ($port->last_checked_at !== null) {
            $query->where(
                'recorded_at',
                '<',
                $port->last_checked_at
            );
        }

        $metric = $query
            ->latest('recorded_at')
            ->first();

        if (!$metric) {
            return null;
        }

        return (float) $metric->value;
    }

    private function counterDelta(
        int $previous,
        int $current
    ): int {
        /*
         * SNMP counters can reset after a device reboot,
         * interface reset, or counter rollover.
         *
         * If current is smaller than previous, treat the
         * current value as the new counter baseline.
         */
        if ($current < $previous) {
            return $current;
        }

        return $current - $previous;
    }

    private function createOrUpdateAlert(
        Port $port,
        string $type,
        string $severity,
        string $metric,
        float $value,
        string $message
    ): Alert {
        $alert = Alert::where(
            'alertable_type',
            Port::class
        )
            ->where(
                'alertable_id',
                $port->id
            )
            ->where(
                'type',
                $type
            )
            ->whereNull('resolved_at')
            ->first();

        if ($alert) {
            $alert->update([
                'severity' => $severity,
                'metric' => $metric,
                'value' => $value,
                'message' => $message,
            ]);

            return $alert;
        }

        return Alert::create([
            'alertable_type' => Port::class,
            'alertable_id' => $port->id,
            'type' => $type,
            'severity' => $severity,
            'metric' => $metric,
            'value' => $value,
            'threshold' => null,
            'message' => $message,
            'triggered_at' => Carbon::now(),
        ]);
    }

    private function resolveAlert(
        Port $port,
        string $type
    ): void {
        Alert::where(
            'alertable_type',
            Port::class
        )
            ->where(
                'alertable_id',
                $port->id
            )
            ->where(
                'type',
                $type
            )
            ->whereNull('resolved_at')
            ->update([
                'resolved_at' => Carbon::now(),
            ]);
    }
}
