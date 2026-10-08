<?php

namespace App\Console\Commands;

use App\Models\Host;
use Illuminate\Console\Command;

class CheckHosts extends Command
{
    protected $signature = 'minimon:hosts';

    protected $description =
        'Mark hosts offline when their agent stops reporting';

    public function handle(): int
    {
        $cutoff = now()->subMinutes(2);

        $hosts = Host::where(function ($query) use ($cutoff) {
            $query->whereNull('last_seen_at')
                ->orWhere(
                    'last_seen_at',
                    '<',
                    $cutoff
                );
        })
            ->where('status', '!=', 'offline')
            ->get();

        foreach ($hosts as $host) {
            $host->update([
                'status' => 'offline',
            ]);

            $this->warn(
                "OFFLINE: {$host->name}"
            );
        }

        if ($hosts->isEmpty()) {
            $this->info(
                'All hosts are reporting normally.'
            );
        }

        return self::SUCCESS;
    }
}