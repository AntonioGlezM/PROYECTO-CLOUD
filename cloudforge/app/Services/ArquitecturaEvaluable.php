<?php

namespace App\Services;

/**
 * Los datos de una arquitectura ya preparados para pasarlos por las reglas.
 * No usa Eloquent, asi en los tests se le pueden meter datos inventados
 * sin tocar la base de datos.
 */
readonly class ArquitecturaEvaluable
{
    /**
     * @param  array<int, array{nombre:string, tipo:string, region:string, disponible:bool}>  $elementos
     * @param  array<int, array{clave:string, operador:string, valor:string, tipo:string, obligatorio:bool}>  $requerimientos
     * @param  array<string, mixed>  $hechos  totales de la arquitectura (ram, cpu, regiones...)
     */
    public function __construct(
        public array $elementos,
        public array $requerimientos,
        public array $hechos,
        public int $totalMensualCentimos,
        public int $presupuestoMaxCentimos,
    ) {}

    // tipos de producto que hay, sin repetir
    public function tipos(): array
    {
        return array_values(array_unique(array_column($this->elementos, 'tipo')));
    }

    // regiones que se usan, sin repetir
    public function regiones(): array
    {
        return array_values(array_unique(array_column($this->elementos, 'region')));
    }
}
