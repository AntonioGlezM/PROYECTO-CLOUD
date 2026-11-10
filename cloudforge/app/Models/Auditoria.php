<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';

    protected $fillable = ['actor', 'accion', 'entidad', 'entidad_id', 'datos'];

    protected function casts(): array
    {
        return ['datos' => 'array'];
    }

    /** Atajo para dejar constancia de una operación. */
    public static function registrar(string $accion, string $entidad, ?int $id = null, array $datos = []): void
    {
        self::create([
            'accion'     => $accion,
            'entidad'    => $entidad,
            'entidad_id' => $id,
            'datos'      => $datos,
        ]);
    }
}
