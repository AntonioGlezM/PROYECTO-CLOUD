<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyecto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'cliente', 'descripcion', 'presupuesto_max_centimos', 'region_preferida_id',
    ];

    protected function casts(): array
    {
        return ['presupuesto_max_centimos' => 'integer'];
    }

    public function regionPreferida(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_preferida_id');
    }

    public function requerimientos(): HasMany
    {
        return $this->hasMany(Requerimiento::class);
    }

    public function arquitecturas(): HasMany
    {
        return $this->hasMany(Arquitectura::class);
    }

    public function lineasCarrito(): HasMany
    {
        return $this->hasMany(LineaCarrito::class);
    }

    public function presupuestos(): HasMany
    {
        return $this->hasMany(Presupuesto::class);
    }

    public function ficheros(): HasMany
    {
        return $this->hasMany(Fichero::class);
    }

    /** Un proyecto sólo se puede borrar si no tiene presupuestos emitidos. */
    public function sePuedeBorrar(): bool
    {
        return $this->presupuestos()->count() === 0;
    }
}
