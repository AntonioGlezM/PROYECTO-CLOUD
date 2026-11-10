<?php

namespace App\Services;

use App\Enums\Severidad;

// Lo que devuelve una regla cuando encuentra algo en la arquitectura
readonly class Hallazgo
{
    public function __construct(
        public string $regla,        // codigo de la regla (R01 a R06)
        public Severidad $severidad,
        public string $mensaje,      // el texto que sale en pantalla
        public array $detalle = [],  // datos extra para la vista y los tests
    ) {}

    public function bloquea(): bool
    {
        return $this->severidad->bloquea();
    }
}
