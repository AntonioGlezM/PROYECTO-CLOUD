<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElementoArquitectura extends Model
{
    use HasFactory;

    protected $table = 'elementos_arquitectura';

    protected $fillable = [
        'arquitectura_id', 'producto_id', 'region_id',
        'cantidad', 'consumo_estimado', 'configuracion',
    ];

    protected function casts(): array
    {
        return [
            // Cada tipo de producto tiene atributos distintos, así que se
            // guardan como JSON en lugar de crear una columna por atributo.
            'configuracion'    => 'array',
            'cantidad'         => 'integer',
            'consumo_estimado' => 'integer',
        ];
    }

    public function arquitectura(): BelongsTo
    {
        return $this->belongsTo(Arquitectura::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
