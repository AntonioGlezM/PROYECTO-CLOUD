<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuestos', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->foreignId('proyecto_id')->constrained('proyectos')->restrictOnDelete();
            // La arquitectura puede desaparecer; el presupuesto emitido no.
            $tabla->foreignId('arquitectura_id')->nullable()
                  ->constrained('arquitecturas')->nullOnDelete();
            $tabla->string('numero', 30)->unique();
            $tabla->timestamp('emitido_en');
            $tabla->unsignedBigInteger('total_mensual_centimos');
            $tabla->unsignedBigInteger('total_unico_centimos');
            // Avisos que no bloquearon la emisión, guardados tal cual se mostraron.
            $tabla->json('avisos')->nullable();
            $tabla->timestamps();

            $tabla->index(['proyecto_id', 'emitido_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
    }
};
