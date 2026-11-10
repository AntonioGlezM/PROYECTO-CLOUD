<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regiones', function (Blueprint $tabla) {
            $tabla->id();
            // Una región pertenece a un proveedor: eu-west-1 es de AWS y no existe en Azure.
            $tabla->foreignId('proveedor_id')->constrained('proveedores')->cascadeOnDelete();
            $tabla->string('nombre', 80);
            $tabla->string('codigo', 30);
            $tabla->string('pais', 60);
            $tabla->timestamps();

            $tabla->unique(['proveedor_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regiones');
    }
};
