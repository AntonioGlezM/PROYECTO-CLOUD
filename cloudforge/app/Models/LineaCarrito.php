<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineaCarrito extends Model
{
    use HasFactory;

    protected $table = 'lineas_carrito';

    protected $fillable = [
        'proyecto_id', 'producto_id', 'region_id',
        'cantidad', 'consumo_estimado', 'configuracion',
    ];

    protected function casts(): array
    {
        return [
            'configuracion'    => 'array',
            'cantidad'         => 'integer',
            'consumo_estimado' => 'integer',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
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
