<?php

namespace App\Services;

class PingService
{
    public function check(
        string $ip,
        int $timeout = 2
    ): bool {
        if (PHP_OS_FAMILY === 'Windows') {
            return $this->windowsPing($ip, $timeout);
        }

        return $this->linuxPing($ip, $timeout);
    }

    private function windowsPing(
        string $ip,
        int $timeout
    ): bool {
        $timeoutMs = $timeout * 1000;

        $command = sprintf(
            'ping -n 1 -w %d %s',
            $timeoutMs,
            escapeshellarg($ip)
        );

        exec(
            $command,
            $output,
            $result
        );

        return $result === 0;
    }

    private function linuxPing(
        string $ip,
        int $timeout
    ): bool {
        $command = sprintf(
            'ping -c 1 -W %d %s',
            $timeout,
            escapeshellarg($ip)
        );

        exec(
            $command,
            $output,
            $result
        );

        return $result === 0;
    }
}