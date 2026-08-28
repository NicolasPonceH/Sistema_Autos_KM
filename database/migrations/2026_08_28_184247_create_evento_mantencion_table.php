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
        Schema::create('evento_mantencion', function (Blueprint $table) {
            $table->id();
            $table->string('patente', 10);
            $table->foreign('patente')->references('patente')->on('vehiculo');
            $table->foreignId('plan_id')->constrained('plan_mantencion');
            $table->integer('km_evento');
            $table->date('fecha');
            $table->decimal('costo', 12, 0)->nullable();
            $table->string('taller', 100)->nullable();
            $table->text('notas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento_mantencion');
    }
};
