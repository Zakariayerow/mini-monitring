<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Threshold;
use Illuminate\Support\Carbon;

class AlertService
{
    public function evaluate(
        string $metric,
        float $value,
        ?string $alertableType = null,
        ?int $alertableId = null
    ): string {
        $threshold = Threshold::where('metric', $metric)
            ->where('enabled', true)
            ->first();

        if (!$threshold) {
            return 'unknown';
        }

        if ($value > $threshold->critical) {
            $severity = 'critical';
        } elseif ($value > $threshold->warning) {
            $severity = 'warning';
        } else {
            $severity = 'normal';
        }

        if ($severity === 'normal') {
            $this->resolveExistingAlert(
                $metric,
                $alertableType,
                $alertableId
            );

            return 'normal';
        }

        $this->createOrUpdateAlert(
            $metric,
            $value,
            $threshold,
            $severity,
            $alertableType,
            $alertableId
        );

        return $severity;
    }

    private function createOrUpdateAlert(
        string $metric,
        float $value,
        Threshold $threshold,
        string $severity,
        ?string $alertableType,
        ?int $alertableId
    ): Alert {
        $query = Alert::where('metric', $metric)
            ->whereNull('resolved_at');

        if ($alertableType !== null && $alertableId !== null) {
            $query->where('alertable_type', $alertableType)
                ->where('alertable_id', $alertableId);
        }

        $alert = $query->first();

        if ($alert) {
            $alert->update([
                'type' => 'threshold',
                'severity' => $severity,
                'value' => $value,
                'threshold' => $this->getThresholdValue(
                    $threshold,
                    $severity
                ),
                'message' => sprintf(
                    '%s exceeded the %s threshold. Current value: %s%%',
                    $threshold->name,
                    $severity,
                    $value
                ),
            ]);

            return $alert;
        }

        return Alert::create([
            'alertable_type' => $alertableType,
            'alertable_id' => $alertableId,
            'type' => 'threshold',
            'severity' => $severity,
            'metric' => $metric,
            'value' => $value,
            'threshold' => $this->getThresholdValue(
                $threshold,
                $severity
            ),
            'message' => sprintf(
                '%s exceeded the %s threshold. Current value: %s%%',
                $threshold->name,
                $severity,
                $value
            ),
            'triggered_at' => Carbon::now(),
        ]);
    }

    private function resolveExistingAlert(
        string $metric,
        ?string $alertableType,
        ?int $alertableId
    ): void {
        $query = Alert::where('metric', $metric)
            ->whereNull('resolved_at');

        if ($alertableType !== null && $alertableId !== null) {
            $query->where('alertable_type', $alertableType)
                ->where('alertable_id', $alertableId);
        }

        $query->update([
            'resolved_at' => Carbon::now(),
        ]);
    }

    private function getThresholdValue(
        Threshold $threshold,
        string $severity
    ): float {
        return $severity === 'critical'
            ? $threshold->critical
            : $threshold->warning;
    }
}