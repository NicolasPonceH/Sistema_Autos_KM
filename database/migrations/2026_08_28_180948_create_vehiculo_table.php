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
        Schema::create('vehiculo', function (Blueprint $table) {
            $table->string('patente', 10)->primary();
            $table->string('tipo_codigo', 5);
            $table->foreign('tipo_codigo')->references('codigo')->on('tipo_vehiculo');
            $table->string('marca', 50)->nullable();
            $table->string('modelo', 50);
            $table->smallInteger('anio');
            $table->string('nro_motor', 40)->nullable()->unique();
            $table->string('nro_chasis', 40)->nullable()->unique();
            $table->integer('km_actual')->default(0);
            $table->timestamp('fecha_km')->nullable();
            $table->string('email_contacto', 120);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculo');
    }
};
