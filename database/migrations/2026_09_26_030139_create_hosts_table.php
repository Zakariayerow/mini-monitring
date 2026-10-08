<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hosts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('hostname')->nullable();
            $table->ipAddress('ip_address')->nullable();

            $table->string('agent_key')->unique();

            $table->string('operating_system')->nullable();
            $table->string('architecture')->nullable();

            $table->string('status')->default('unknown');

            $table->decimal('cpu_percent', 5, 2)->nullable();
            $table->decimal('memory_percent', 5, 2)->nullable();
            $table->decimal('disk_percent', 5, 2)->nullable();

            $table->unsignedBigInteger('memory_total')->nullable();
            $table->unsignedBigInteger('memory_used')->nullable();
            $table->unsignedBigInteger('disk_total')->nullable();
            $table->unsignedBigInteger('disk_used')->nullable();

            $table->unsignedBigInteger('uptime')->nullable();

            $table->timestamp('last_seen_at')->nullable();

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hosts');
    }
};