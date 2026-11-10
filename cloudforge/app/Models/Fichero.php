<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fichero extends Model
{
    use HasFactory;

    protected $fillable = ['proyecto_id', 'nombre_original', 'ruta', 'mime', 'tamano'];

    protected function casts(): array
    {
        return ['tamano' => 'integer'];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function tamanoLegible(): string
    {
        $kb = $this->tamano / 1024;

        return $kb < 1024
            ? number_format($kb, 0, ',', '.') . ' KB'
            : number_format($kb / 1024, 2, ',', '.') . ' MB';
    }
}
