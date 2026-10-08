<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('host_id')
                ->nullable()
                ->constrained('hosts')
                ->cascadeOnDelete();

            $table->foreignId('device_id')
                ->nullable()
                ->constrained('devices')
                ->cascadeOnDelete();

            $table->string('metric');
            $table->decimal('value', 20, 4);

            $table->string('unit')->nullable();

            $table->timestamp('recorded_at');

            $table->timestamps();

            $table->index(['host_id', 'metric', 'recorded_at']);
            $table->index(['device_id', 'metric', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};