<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('proveedor_id')->constrained('proveedores')->restrictOnDelete();
            $tabla->foreignId('tipo_producto_id')->constrained('tipos_producto')->restrictOnDelete();
            $tabla->string('nombre', 120);
            $tabla->string('sku', 40)->unique();
            $tabla->text('descripcion')->nullable();
            $tabla->string('modalidad', 20);              // enum Modalidad
            // Los importes se guardan en céntimos como entero: con decimales la suma
            // de las líneas no cuadra con el total por errores de redondeo.
            $tabla->unsignedBigInteger('tarifa_centimos');
            $tabla->string('unidad', 30);                 // instancia/mes, GB/mes, hora...
            // Sólo lo usa la modalidad de consumo: importe máximo facturable al mes.
            $tabla->unsignedBigInteger('tope_mensual_centimos')->nullable();
            $tabla->boolean('activo')->default(true);
            $tabla->timestamps();

            $tabla->index(['tipo_producto_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
