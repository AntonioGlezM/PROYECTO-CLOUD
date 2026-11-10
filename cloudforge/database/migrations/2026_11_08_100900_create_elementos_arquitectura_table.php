<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elementos_arquitectura', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('arquitectura_id')->constrained('arquitecturas')->cascadeOnDelete();
            // No se permite borrar un producto que forma parte de una arquitectura:
            // se desactiva en el catálogo (restrictOnDelete).
            $tabla->foreignId('producto_id')->constrained('productos')->restrictOnDelete();
            $tabla->foreignId('region_id')->constrained('regiones')->restrictOnDelete();
            $tabla->unsignedInteger('cantidad')->default(1);
            // Consumo estimado al mes; sólo lo usa la modalidad de consumo.
            $tabla->unsignedInteger('consumo_estimado')->nullable();
            // Pares atributo/valor libres por elemento. Se guarda como JSON porque
            // cada tipo de producto tiene atributos distintos (ADR-002).
            $tabla->json('configuracion')->nullable();
            $tabla->timestamps();

            $tabla->index('arquitectura_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elementos_arquitectura');
    }
};
