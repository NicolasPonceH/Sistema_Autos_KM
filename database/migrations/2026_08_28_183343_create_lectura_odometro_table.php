<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lectura_odometro', function (Blueprint $table) {
            $table->id();
            $table->string('patente', 10);
            $table->foreign('patente')->references('patente')->on('vehiculo');
            $table->integer('km');
            $table->timestamp('fecha')->useCurrent();
            // plan.md referencia `usuario(id)`, pero esa tabla no existe en ningún
            // lado del plan; el único catálogo real de identidades es el `users`
            // por defecto de Laravel. Se deja nullable: todavía no hay login (Fase 5).
            $table->foreignId('reportado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->string('origen', 20)->default('MANUAL');
            $table->boolean('anulada')->default(false);
            $table->text('observacion')->nullable();
        });

        DB::statement('ALTER TABLE lectura_odometro ADD CONSTRAINT lectura_odometro_km_check CHECK (km >= 0)');
        DB::statement('CREATE INDEX ix_lectura_patente_fecha ON lectura_odometro (patente, fecha DESC)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lectura_odometro');
    }
};
