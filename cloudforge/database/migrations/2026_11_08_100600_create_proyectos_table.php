<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('nombre', 120);
            $tabla->string('cliente', 120);
            $tabla->text('descripcion')->nullable();
            $tabla->unsignedBigInteger('presupuesto_max_centimos');
            // Si el proyecto fija una región preferida, borrarla no debe borrar el
            // proyecto: se deja a null y se avisa.
            $tabla->foreignId('region_preferida_id')->nullable()
                  ->constrained('regiones')->nullOnDelete();
            $tabla->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
