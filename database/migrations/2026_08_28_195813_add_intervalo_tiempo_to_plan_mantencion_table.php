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
        Schema::table('plan_mantencion', function (Blueprint $table) {
            // Fase 5: "cada 10.000 km o 12 meses, lo que ocurra primero".
            // Ambos nullable — un plan sigue pudiendo ser solo por km.
            // umbral_aviso_dias es el equivalente temporal de umbral_aviso,
            // no está en plan.md original pero sigue el mismo principio de
            // las reglas 2/3: nada de umbrales hardcodeados en el código.
            $table->integer('intervalo_meses')->nullable()->after('intervalo_km');
            $table->integer('umbral_aviso_dias')->nullable()->default(30)->after('umbral_aviso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_mantencion', function (Blueprint $table) {
            $table->dropColumn(['intervalo_meses', 'umbral_aviso_dias']);
        });
    }
};
