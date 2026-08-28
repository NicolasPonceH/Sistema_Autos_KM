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
        Schema::create('notificacion_enviada', function (Blueprint $table) {
            $table->id();
            // Sin FK a propósito (igual que el DDL de plan.md): el historial de
            // avisos sobrevive aunque se borre el vehículo o el plan.
            $table->string('patente', 10);
            $table->integer('plan_id');
            $table->integer('km_objetivo');
            $table->timestamp('enviada_en')->useCurrent();
            $table->string('destinatario', 120)->nullable();
            $table->string('estado', 15)->default('ENVIADA');

            // Regla 6: esta es la única defensa contra duplicados. No quitar.
            $table->unique(['patente', 'plan_id', 'km_objetivo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacion_enviada');
    }
};
