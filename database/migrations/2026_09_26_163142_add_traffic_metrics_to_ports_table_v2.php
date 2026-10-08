<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->unsignedBigInteger('rx_bytes')
                ->nullable()
                ->after('bytes_in');

            $table->unsignedBigInteger('tx_bytes')
                ->nullable()
                ->after('bytes_out');

            $table->float('rx_bps')
                ->nullable()
                ->after('tx_bytes');

            $table->float('tx_bps')
                ->nullable()
                ->after('rx_bps');

            $table->float('rx_utilization')
                ->nullable()
                ->after('tx_bps');

            $table->float('tx_utilization')
                ->nullable()
                ->after('rx_utilization');
        });
    }

    public function down(): void
    {
        Schema::table('ports', function (Blueprint $table) {
            $table->dropColumn([
                'rx_bytes',
                'tx_bytes',
                'rx_bps',
                'tx_bps',
                'rx_utilization',
                'tx_utilization',
            ]);
        });
    }
};
