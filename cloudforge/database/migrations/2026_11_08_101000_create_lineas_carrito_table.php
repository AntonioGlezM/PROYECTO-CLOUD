<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // El carrito vive en base de datos y cuelga del proyecto: en UT2 todavía
        // no hay login, así que el proyecto hace de "sesión de trabajo".
        Schema::create('lineas_carrito', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $tabla->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $tabla->foreignId('region_id')->constrained('regiones')->restrictOnDelete();
            $tabla->unsignedInteger('cantidad')->default(1);
            $tabla->unsignedInteger('consumo_estimado')->nullable();
            $tabla->json('configuracion')->nullable();
            $tabla->timestamps();

            $tabla->unique(['proyecto_id', 'producto_id', 'region_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lineas_carrito');
    }
};
