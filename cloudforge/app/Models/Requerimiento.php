<?php

namespace App\Models;

use App\Enums\TipoDato;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requerimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'proyecto_id', 'clave', 'operador', 'valor', 'tipo', 'obligatorio', 'origen',
    ];

    protected function casts(): array
    {
        return [
            'tipo'        => TipoDato::class,
            'obligatorio' => 'boolean',
        ];
    }

    /** Claves admitidas. Son las que el motor de reglas sabe comprobar. */
    public const CLAVES = [
        'cpu_min'                 => 'CPU mínima (vCPU)',
        'ram_min_gb'              => 'RAM mínima (GB)',
        'almacenamiento_min_gb'   => 'Almacenamiento mínimo (GB)',
        'region'                  => 'Región exigida',
        'proveedor'               => 'Proveedor exigido',
        'backup_requerido'        => 'Copia de seguridad obligatoria',
    ];

    /** Tipo de dato que corresponde a cada clave. */
    public const TIPO_POR_CLAVE = [
        'cpu_min'               => 'numero',
        'ram_min_gb'            => 'numero',
        'almacenamiento_min_gb' => 'numero',
        'region'                => 'lista',
        'proveedor'             => 'lista',
        'backup_requerido'      => 'booleano',
    ];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function descripcion(): string
    {
        return (self::CLAVES[$this->clave] ?? $this->clave)
            . ' ' . $this->operador . ' ' . $this->valor;
    }
}
