<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('device_id')
                ->constrained('devices')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('interface_name')->nullable();

            $table->string('if_index')->nullable();

            $table->string('status')->default('unknown');

            $table->unsignedBigInteger('speed')->nullable();

            $table->unsignedBigInteger('bytes_in')->default(0);
            $table->unsignedBigInteger('bytes_out')->default(0);

            $table->unsignedBigInteger('packets_in')->default(0);
            $table->unsignedBigInteger('packets_out')->default(0);

            $table->unsignedBigInteger('errors_in')->default(0);
            $table->unsignedBigInteger('errors_out')->default(0);

            $table->timestamp('last_checked_at')->nullable();

            $table->timestamps();

            $table->index(['device_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
    }
};