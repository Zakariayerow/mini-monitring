<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thresholds', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('metric');

            $table->decimal('warning', 10, 2)->nullable();
            $table->decimal('critical', 10, 2)->nullable();

            $table->string('operator')->default('>');

            $table->boolean('enabled')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique('metric');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thresholds');
    }
};