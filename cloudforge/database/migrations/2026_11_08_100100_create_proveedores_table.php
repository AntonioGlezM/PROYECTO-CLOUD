<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('nombre', 80);
            $tabla->string('codigo', 20)->unique();   // identificador corto: aws, azure
            $tabla->boolean('activo')->default(true);
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
