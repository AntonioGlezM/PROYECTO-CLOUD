<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Copia histórica: el presupuesto emitido no debe cambiar aunque después
        // se modifique el catálogo, así que aquí no hay FK al producto, sino el
        // nombre, el tipo y la tarifa copiados en el momento de emitir.
        Schema::create('lineas_presupuesto', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('presupuesto_id')->constrained('presupuestos')->cascadeOnDelete();
            $tabla->unsignedBigInteger('producto_id_original')->nullable();  // sólo trazabilidad
            $tabla->string('producto_nombre', 120);
            $tabla->string('producto_sku', 40);
            $tabla->string('tipo_nombre', 60);
            $tabla->string('proveedor_nombre', 80);
            $tabla->string('region_nombre', 80);
            $tabla->string('modalidad', 20);
            $tabla->string('unidad', 30);
            $tabla->unsignedInteger('cantidad');
            $tabla->unsignedInteger('consumo_estimado')->nullable();
            $tabla->unsignedBigInteger('tarifa_centimos');
            $tabla->unsignedBigInteger('tope_mensual_centimos')->nullable();
            $tabla->unsignedBigInteger('importe_centimos');
            $tabla->boolean('recurrente');
            $tabla->json('configuracion')->nullable();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lineas_presupuesto');
    }
};
