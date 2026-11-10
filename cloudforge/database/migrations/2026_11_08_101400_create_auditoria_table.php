<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Registro de las operaciones relevantes. En UT2 no hay login, así que el
        // actor se guarda como texto; en UT3 pasará a ser una FK a usuarios.
        Schema::create('auditoria', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->string('actor', 80)->default('desarrollo');
            $tabla->string('accion', 60);
            $tabla->string('entidad', 60);
            $tabla->unsignedBigInteger('entidad_id')->nullable();
            $tabla->json('datos')->nullable();
            $tabla->timestamps();

            $tabla->index(['entidad', 'entidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
