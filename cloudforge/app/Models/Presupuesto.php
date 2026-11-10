<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Presupuesto emitido. Es un documento histórico: una vez emitido no cambia
 * aunque después se modifique el catálogo. Por eso sus líneas no apuntan al
 * producto, sino que guardan una copia de sus datos.
 */
class Presupuesto extends Model
{
    use HasFactory;

    protected $fillable = [
        'proyecto_id', 'arquitectura_id', 'numero', 'emitido_en',
        'total_mensual_centimos', 'total_unico_centimos', 'avisos',
    ];

    protected function casts(): array
    {
        return [
            'emitido_en'             => 'datetime',
            'avisos'                 => 'array',
            'total_mensual_centimos' => 'integer',
            'total_unico_centimos'   => 'integer',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function arquitectura(): BelongsTo
    {
        return $this->belongsTo(Arquitectura::class);
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(LineaPresupuesto::class);
    }

    /** Coste del primer mes: lo recurrente más lo que se paga una sola vez. */
    public function totalPrimerMesCentimos(): int
    {
        return $this->total_mensual_centimos + $this->total_unico_centimos;
    }
}
