<?php

namespace App\Models;

use App\Enums\Modalidad;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'proveedor_id', 'tipo_producto_id', 'nombre', 'sku', 'descripcion',
        'modalidad', 'tarifa_centimos', 'unidad', 'tope_mensual_centimos', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'modalidad'             => Modalidad::class,
            'tarifa_centimos'       => 'integer',
            'tope_mensual_centimos' => 'integer',
            'activo'                => 'boolean',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_producto_id');
    }

    public function regiones(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'producto_region')
                    ->withPivot(['disponible', 'tarifa_centimos'])
                    ->withTimestamps();
    }

    /** Tarifa que se aplica en una región concreta, con la base como respaldo. */
    public function tarifaEnRegion(int $regionId): int
    {
        $pivot = $this->regiones->firstWhere('id', $regionId)?->pivot;

        return $pivot?->tarifa_centimos ?? $this->tarifa_centimos;
    }

    public function estaDisponibleEn(int $regionId): bool
    {
        return (bool) ($this->regiones->firstWhere('id', $regionId)?->pivot->disponible ?? false);
    }

    // ------------------------------------------------- filtros reutilizables

    public function scopeActivos(Builder $q): Builder
    {
        return $q->where('activo', true);
    }

    public function scopeDeTipo(Builder $q, ?int $tipoId): Builder
    {
        return $tipoId ? $q->where('tipo_producto_id', $tipoId) : $q;
    }

    public function scopeDeProveedor(Builder $q, ?int $proveedorId): Builder
    {
        return $proveedorId ? $q->where('proveedor_id', $proveedorId) : $q;
    }

    public function scopeBuscar(Builder $q, ?string $texto): Builder
    {
        if (! $texto) {
            return $q;
        }

        return $q->where(function (Builder $sub) use ($texto) {
            $sub->where('nombre', 'like', "%{$texto}%")
                ->orWhere('sku', 'like', "%{$texto}%");
        });
    }

    /** Sólo productos ofrecidos y disponibles en la región indicada. */
    public function scopeDisponibleEnRegion(Builder $q, ?int $regionId): Builder
    {
        if (! $regionId) {
            return $q;
        }

        return $q->whereHas('regiones', function (Builder $sub) use ($regionId) {
            $sub->where('regiones.id', $regionId)
                ->where('producto_region.disponible', true);
        });
    }
}
