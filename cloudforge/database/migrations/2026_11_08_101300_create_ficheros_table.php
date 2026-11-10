<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Documentación adjunta a un proyecto (pliego, plano de red, factura previa).
        Schema::create('ficheros', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $tabla->string('nombre_original', 180);
            $tabla->string('ruta', 255);          // ruta dentro del disco 'local'
            $tabla->string('mime', 100);
            $tabla->unsignedBigInteger('tamano');
            $tabla->timestamps();

            $tabla->index('proyecto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficheros');
    }
};
