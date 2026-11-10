<?php

namespace App\Services;

use App\Models\Arquitectura;
use App\Models\Proyecto;
use Illuminate\Support\Collection;

/**
 * Coge los datos de la base de datos (carrito o arquitectura) y los pasa al
 * formato que usa el motor de reglas (ArquitecturaEvaluable). Es la unica
 * clase que trabaja con Eloquent y con las reglas a la vez, asi las reglas
 * se pueden probar por separado.
 */
class ConstructorEvaluable
{
    public function __construct(private CalculadoraPrecios $precios) {}

    public function desdeCarrito(Proyecto $proyecto): ArquitecturaEvaluable
    {
        $proyecto->loadMissing([
            'lineasCarrito.producto.tipo',
            'lineasCarrito.producto.regiones',
            'lineasCarrito.region',
            'requerimientos',
        ]);

        return $this->construir($proyecto, $proyecto->lineasCarrito);
    }

    public function desdeArquitectura(Arquitectura $arquitectura): ArquitecturaEvaluable
    {
        $arquitectura->loadMissing([
            'elementos.producto.tipo',
            'elementos.producto.regiones',
            'elementos.region',
            'proyecto.requerimientos',
        ]);

        return $this->construir($arquitectura->proyecto, $arquitectura->elementos);
    }

    /** @param Collection<int, \App\Models\LineaCarrito|\App\Models\ElementoArquitectura> $lineas */
    private function construir(Proyecto $proyecto, Collection $lineas): ArquitecturaEvaluable
    {
        $elementos = [];
        $calculadas = [];

        foreach ($lineas as $linea) {
            $producto = $linea->producto;
            $tarifa   = $producto->tarifaEnRegion($linea->region_id);

            $elementos[] = [
                'nombre'     => $producto->nombre,
                'tipo'       => $producto->tipo->codigo,
                'region'     => $linea->region->codigo,
                'disponible' => $producto->estaDisponibleEn($linea->region_id),
            ];

            $calculadas[] = $this->precios->linea(
                $producto->modalidad,
                $tarifa,
                $linea->cantidad,
                $linea->consumo_estimado,
                $producto->tope_mensual_centimos,
            );
        }

        $totales = $this->precios->totales($calculadas);

        return new ArquitecturaEvaluable(
            elementos: $elementos,
            requerimientos: $proyecto->requerimientos->map(fn ($r) => [
                'clave'       => $r->clave,
                'operador'    => $r->operador,
                'valor'       => $r->valor,
                'tipo'        => $r->tipo->value,
                'obligatorio' => $r->obligatorio,
            ])->all(),
            hechos: $this->hechos($lineas),
            totalMensualCentimos: $totales['mensual_centimos'],
            presupuestoMaxCentimos: $proyecto->presupuesto_max_centimos,
        );
    }

    /**
     * Suma la cpu, ram y almacenamiento de todos los elementos y saca las
     * regiones y proveedores, para luego compararlo con los requisitos.
     *
     * @return array<string, mixed>
     */
    private function hechos(Collection $lineas): array
    {
        $cpu = 0;
        $ram = 0;
        $alm = 0;
        $regiones = [];
        $proveedores = [];
        $hayBackup = false;

        foreach ($lineas as $linea) {
            $config   = $linea->configuracion ?? [];
            $cantidad = $linea->cantidad;

            $cpu += ((int) ($config['vcpu'] ?? 0)) * $cantidad;
            $ram += ((int) ($config['ram_gb'] ?? 0)) * $cantidad;
            $alm += ((int) ($config['almacenamiento_gb'] ?? 0)) * $cantidad;

            $regiones[]    = $linea->region->codigo;
            $proveedores[] = $linea->producto->proveedor->codigo;

            if ($linea->producto->tipo->codigo === 'backup') {
                $hayBackup = true;
            }
        }

        return [
            'cpu_min'               => $cpu,
            'ram_min_gb'            => $ram,
            'almacenamiento_min_gb' => $alm,
            'region'                => array_values(array_unique($regiones)),
            'proveedor'             => array_values(array_unique($proveedores)),
            'backup_requerido'      => $hayBackup,
        ];
    }
}
