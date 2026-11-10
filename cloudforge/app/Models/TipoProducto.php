<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoProducto extends Model
{
    use HasFactory;

    protected $table = 'tipos_producto';

    protected $fillable = ['nombre', 'codigo'];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
