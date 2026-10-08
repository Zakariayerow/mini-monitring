<?php

namespace App\Services;

class SnmpService
{
    public function get(
        string $ip,
        string $community,
        string $oid,
        int $port = 161
    ): mixed {
        if (!function_exists('snmp2_get')) {
            throw new \RuntimeException(
                'PHP SNMP extension is not enabled.'
            );
        }

        $result = @snmp2_get(
            $ip . ':' . $port,
            $community,
            $oid,
            5000000,
            1
        );

        if ($result === false) {
            throw new \RuntimeException(
                "SNMP request failed for {$ip}:{$port}"
            );
        }

        return $result;
    }

    public function walk(
        string $ip,
        string $community,
        string $oid,
        int $port = 161
    ): array {
        if (!function_exists('snmp2_real_walk')) {
            throw new \RuntimeException(
                'PHP SNMP extension is not enabled.'
            );
        }

        $result = @snmp2_real_walk(
            $ip . ':' . $port,
            $community,
            $oid,
            5000000,
            1
        );

        if ($result === false) {
            throw new \RuntimeException(
                "SNMP walk failed for {$ip}:{$port}"
            );
        }

        return $result;
    }
}