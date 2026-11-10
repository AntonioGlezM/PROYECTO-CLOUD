<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla de disponibilidad: un producto puede ofrecerse en varias regiones,
        // con un precio distinto en cada una, y no estar disponible en otras (ADR-001).
        Schema::create('producto_region', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $tabla->foreignId('region_id')->constrained('regiones')->cascadeOnDelete();
            $tabla->boolean('disponible')->default(true);
            // Si es null se aplica la tarifa base del producto.
            $tabla->unsignedBigInteger('tarifa_centimos')->nullable();
            $tabla->timestamps();

            $tabla->unique(['producto_id', 'region_id']);
            $tabla->index(['region_id', 'disponible']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_region');
    }
};
