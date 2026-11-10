<?php

namespace App\Models;

use App\Enums\Modalidad;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaPresupuesto extends Model
{
    use HasFactory;

    protected $table = 'lineas_presupuesto';

    protected $fillable = [
        'presupuesto_id', 'producto_id_original', 'producto_nombre', 'producto_sku',
        'tipo_nombre', 'proveedor_nombre', 'region_nombre', 'modalidad', 'unidad',
        'cantidad', 'consumo_estimado', 'tarifa_centimos', 'tope_mensual_centimos',
        'importe_centimos', 'recurrente', 'configuracion',
    ];

    protected function casts(): array
    {
        return [
            'modalidad'             => Modalidad::class,
            'configuracion'         => 'array',
            'recurrente'            => 'boolean',
            'cantidad'              => 'integer',
            'tarifa_centimos'       => 'integer',
            'importe_centimos'      => 'integer',
            'tope_mensual_centimos' => 'integer',
        ];
    }

    public function presupuesto(): BelongsTo
    {
        return $this->belongsTo(Presupuesto::class);
    }
}
