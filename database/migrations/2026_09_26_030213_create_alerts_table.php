<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('alertable');

            $table->string('type');
            $table->string('severity')->default('warning');

            $table->string('metric')->nullable();
            $table->decimal('value', 20, 4)->nullable();
            $table->decimal('threshold', 20, 4)->nullable();

            $table->string('message');

            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['severity', 'resolved_at']);
            $table->index(['type', 'resolved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};