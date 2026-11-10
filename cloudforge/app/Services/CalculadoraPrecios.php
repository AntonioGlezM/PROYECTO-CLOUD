<?php

namespace App\Services;

use App\Enums\Modalidad;

/**
 * Aqui se calculan todos los precios. Las vistas y el navegador no calculan
 * nada, solo muestran lo que sale de esta clase.
 *
 * Todo va en centimos y con enteros, porque con float al sumar muchas lineas
 * el total acaba sin cuadrar por los decimales.
 */
class CalculadoraPrecios
{
    /**
     * Calcula lo que cuesta una linea segun su modalidad.
     *
     * @return array{importe_centimos:int, recurrente:bool}
     */
    public function linea(
        Modalidad $modalidad,
        int $tarifaCentimos,
        int $cantidad,
        ?int $consumoEstimado = null,
        ?int $topeMensualCentimos = null,
    ): array {
        if ($tarifaCentimos < 0 || $cantidad < 1) {
            throw new \InvalidArgumentException('Tarifa negativa o cantidad menor que 1.');
        }

        $importe = match ($modalidad) {
            // mensual y unico se calculan igual, solo cambia en que total suman
            Modalidad::Mensual, Modalidad::Unico => $tarifaCentimos * $cantidad,

            // por consumo: tarifa x consumo estimado, y si el producto tiene
            // tope mensual nunca se pasa de ese tope
            Modalidad::Consumo => $this->importePorConsumo(
                $tarifaCentimos, $cantidad, $consumoEstimado, $topeMensualCentimos
            ),
        };

        return [
            'importe_centimos' => $importe,
            'recurrente'       => $modalidad->esRecurrente(),
        ];
    }

    private function importePorConsumo(
        int $tarifaCentimos,
        int $cantidad,
        ?int $consumoEstimado,
        ?int $topeMensualCentimos,
    ): int {
        if ($consumoEstimado === null || $consumoEstimado < 0) {
            throw new \InvalidArgumentException(
                'La modalidad por consumo necesita un consumo estimado.'
            );
        }

        $bruto = $tarifaCentimos * $consumoEstimado * $cantidad;

        // el tope es por cada unidad y por mes
        if ($topeMensualCentimos !== null) {
            $maximo = $topeMensualCentimos * $cantidad;

            return min($bruto, $maximo);
        }

        return $bruto;
    }

    /**
     * Suma las lineas separando lo que se paga cada mes de lo que se paga
     * una sola vez.
     *
     * @param  array<int, array{importe_centimos:int, recurrente:bool}>  $lineas
     * @return array{mensual_centimos:int, unico_centimos:int}
     */
    public function totales(array $lineas): array
    {
        $mensual = 0;
        $unico   = 0;

        foreach ($lineas as $linea) {
            if ($linea['recurrente']) {
                $mensual += $linea['importe_centimos'];
            } else {
                $unico += $linea['importe_centimos'];
            }
        }

        return ['mensual_centimos' => $mensual, 'unico_centimos' => $unico];
    }

    // pasa los centimos a euros para enseñarlos (solo para mostrar, no para calcular)
    public static function euros(int $centimos): string
    {
        return number_format($centimos / 100, 2, ',', '.') . ' €';
    }
}
