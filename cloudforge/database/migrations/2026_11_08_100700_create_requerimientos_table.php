<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requerimientos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $tabla->string('clave', 40);        // ram_min_gb, region, backup_requerido...
            $tabla->string('operador', 4);      // >=, <=, =, !=, in
            $tabla->string('valor', 120);
            $tabla->string('tipo', 20);         // enum TipoDato
            $tabla->boolean('obligatorio')->default(true);
            $tabla->string('origen', 20)->default('cliente');  // cliente | equipo | ia
            $tabla->timestamps();

            $tabla->index(['proyecto_id', 'obligatorio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requerimientos');
    }
};
