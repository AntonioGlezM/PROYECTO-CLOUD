<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Presupuesto;
use App\Models\Proyecto;
use Illuminate\Support\Facades\DB;

/**
 * Emite un presupuesto a partir del carrito de un proyecto.
 *
 * La emisión escribe en tres sitios (cabecera, líneas y auditoría) y vacía el
 * carrito, así que va dentro de una transacción: si algo falla a mitad no
 * puede quedar una cabecera sin líneas ni un carrito vaciado sin documento.
 */
class EmisorPresupuestos
{
    public function __construct(
        private CalculadoraPrecios $precios,
        private MotorReglas $reglas,
        private ConstructorEvaluable $constructor,
    ) {}

    /**
     * @throws PresupuestoBloqueado si alguna regla de severidad error se incumple
     */
    public function emitir(Proyecto $proyecto): Presupuesto
    {
        $proyecto->loadMissing([
            'lineasCarrito.producto.tipo',
            'lineasCarrito.producto.proveedor',
            'lineasCarrito.producto.regiones',
            'lineasCarrito.region',
            'requerimientos',
        ]);

        if ($proyecto->lineasCarrito->isEmpty()) {
            throw new PresupuestoBloqueado([new Hallazgo(
                'R00',
                \App\Enums\Severidad::Error,
                'El carrito está vacío: no hay nada que presupuestar.',
            )]);
        }

        $evaluable = $this->constructor->desdeCarrito($proyecto);
        $hallazgos = $this->reglas->evaluar($evaluable);

        if ($this->reglas->hayBloqueo($hallazgos)) {
            throw new PresupuestoBloqueado($hallazgos);
        }

        $avisos = array_values(array_map(
            fn (Hallazgo $h) => ['regla' => $h->regla, 'mensaje' => $h->mensaje],
            array_filter($hallazgos, fn (Hallazgo $h) => ! $h->bloquea()),
        ));

        return DB::transaction(function () use ($proyecto, $avisos) {
            $presupuesto = Presupuesto::create([
                'proyecto_id'            => $proyecto->id,
                'arquitectura_id'        => null,
                'numero'                 => $this->siguienteNumero(),
                'emitido_en'             => now(),
                'total_mensual_centimos' => 0,
                'total_unico_centimos'   => 0,
                'avisos'                 => $avisos,
            ]);

            $calculadas = [];

            foreach ($proyecto->lineasCarrito as $linea) {
                $producto = $linea->producto;
                $tarifa   = $producto->tarifaEnRegion($linea->region_id);

                $calculo = $this->precios->linea(
                    $producto->modalidad,
                    $tarifa,
                    $linea->cantidad,
                    $linea->consumo_estimado,
                    $producto->tope_mensual_centimos,
                );

                $calculadas[] = $calculo;

                // Copia histórica: nombre, tipo, proveedor, región y tarifa
                // quedan congelados. Si mañana cambia el catálogo, este
                // presupuesto sigue diciendo lo mismo.
                $presupuesto->lineas()->create([
                    'producto_id_original'  => $producto->id,
                    'producto_nombre'       => $producto->nombre,
                    'producto_sku'          => $producto->sku,
                    'tipo_nombre'           => $producto->tipo->nombre,
                    'proveedor_nombre'      => $producto->proveedor->nombre,
                    'region_nombre'         => $linea->region->nombre,
                    'modalidad'             => $producto->modalidad->value,
                    'unidad'                => $producto->unidad,
                    'cantidad'              => $linea->cantidad,
                    'consumo_estimado'      => $linea->consumo_estimado,
                    'tarifa_centimos'       => $tarifa,
                    'tope_mensual_centimos' => $producto->tope_mensual_centimos,
                    'importe_centimos'      => $calculo['importe_centimos'],
                    'recurrente'            => $calculo['recurrente'],
                    'configuracion'         => $linea->configuracion,
                ]);
            }

            $totales = $this->precios->totales($calculadas);

            $presupuesto->update([
                'total_mensual_centimos' => $totales['mensual_centimos'],
                'total_unico_centimos'   => $totales['unico_centimos'],
            ]);

            Auditoria::registrar('emitir_presupuesto', 'presupuestos', $presupuesto->id, [
                'numero'  => $presupuesto->numero,
                'mensual' => $totales['mensual_centimos'],
                'unico'   => $totales['unico_centimos'],
            ]);

            // El carrito se vacía: lo seleccionado ya está en el documento.
            $proyecto->lineasCarrito()->delete();

            return $presupuesto->fresh('lineas');
        });
    }

    /** Numeración correlativa por año: PRE-2026-0001. */
    private function siguienteNumero(): string
    {
        $anio  = now()->year;
        $ultimo = Presupuesto::where('numero', 'like', "PRE-{$anio}-%")
                             ->orderByDesc('id')->value('numero');

        $siguiente = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        return sprintf('PRE-%d-%04d', $anio, $siguiente);
    }
}
