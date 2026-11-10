<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regiones';

    protected $fillable = ['proveedor_id', 'nombre', 'codigo', 'pais'];

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    /** Relación N:M con datos propios: disponibilidad y tarifa por región. */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_region')
                    ->withPivot(['disponible', 'tarifa_centimos'])
                    ->withTimestamps();
    }
}
