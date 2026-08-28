<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plan_mantencion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->integer('intervalo_km');
            $table->integer('umbral_aviso')->default(500);
            $table->string('tipo_codigo', 5)->nullable();
            $table->foreign('tipo_codigo')->references('codigo')->on('tipo_vehiculo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_mantencion');
    }
};
