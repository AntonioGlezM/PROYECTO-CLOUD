<?php

namespace App\Models;

use App\Enums\EstadoArquitectura;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arquitectura extends Model
{
    use HasFactory;

    protected $fillable = ['proyecto_id', 'nombre', 'version', 'estado', 'motivo_rechazo'];

    protected function casts(): array
    {
        return ['estado' => EstadoArquitectura::class, 'version' => 'integer'];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function elementos(): HasMany
    {
        return $this->hasMany(ElementoArquitectura::class);
    }

    public function presupuestos(): HasMany
    {
        return $this->hasMany(Presupuesto::class);
    }
}
