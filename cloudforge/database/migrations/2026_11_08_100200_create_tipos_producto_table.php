<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_producto', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('nombre', 60);
            $tabla->string('codigo', 30)->unique();   // computo, almacenamiento, bbdd...
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_producto');
    }
};
