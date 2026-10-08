<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('hostname')->nullable();
            $table->ipAddress('ip_address');

            $table->string('type')->default('network');
            $table->string('vendor')->nullable();
            $table->string('model')->nullable();
            $table->string('operating_system')->nullable();

            $table->string('status')->default('unknown');

            $table->integer('snmp_port')->default(161);
            $table->string('snmp_version')->default('2c');
            $table->string('snmp_community')->nullable();

            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_up_at')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};