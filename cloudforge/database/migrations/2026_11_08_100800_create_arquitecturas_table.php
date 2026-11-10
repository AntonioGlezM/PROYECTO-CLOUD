<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arquitecturas', function (Blueprint $tabla) {
            $tabla->id();
            // Un proyecto admite varias arquitecturas para poder compararlas.
            $tabla->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $tabla->string('nombre', 120);
            $tabla->unsignedInteger('version')->default(1);
            $tabla->string('estado', 20)->default('borrador');   // enum EstadoArquitectura
            $tabla->text('motivo_rechazo')->nullable();
            $tabla->timestamps();

            $tabla->index(['proyecto_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arquitecturas');
    }
};
