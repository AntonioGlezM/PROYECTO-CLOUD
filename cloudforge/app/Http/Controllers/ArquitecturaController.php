<?php

namespace App\Http\Controllers;

use App\Enums\EstadoArquitectura;
use App\Models\Arquitectura;
use App\Models\Proyecto;
use App\Services\ConstructorEvaluable;
use App\Services\MotorReglas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArquitecturaController extends Controller
{
    /** Guarda la selección actual como una arquitectura con nombre. */
    public function store(Request $peticion, Proyecto $proyecto, MotorReglas $reglas, ConstructorEvaluable $constructor)
    {
        $datos = $peticion->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:120'],
        ]);

        if ($proyecto->lineasCarrito()->count() === 0) {
            return back()->with('error', 'No hay nada seleccionado que guardar.');
        }

        $arquitectura = DB::transaction(function () use ($proyecto, $datos) {
            $version = $proyecto->arquitecturas()->max('version') + 1;

            $arquitectura = $proyecto->arquitecturas()->create([
                'nombre'  => $datos['nombre'],
                'version' => $version,
                'estado'  => EstadoArquitectura::Borrador,
            ]);

            foreach ($proyecto->lineasCarrito as $linea) {
                $arquitectura->elementos()->create([
                    'producto_id'      => $linea->producto_id,
                    'region_id'        => $linea->region_id,
                    'cantidad'         => $linea->cantidad,
                    'consumo_estimado' => $linea->consumo_estimado,
                    'configuracion'    => $linea->configuracion,
                ]);
            }

            return $arquitectura;
        });

        // Validarla deja constancia de si es aceptable o por qué no lo es.
        $hallazgos = $reglas->evaluar($constructor->desdeArquitectura($arquitectura));
        $bloqueos  = array_filter($hallazgos, fn ($h) => $h->bloquea());

        $arquitectura->update([
            'estado' => $bloqueos ? EstadoArquitectura::Rechazada : EstadoArquitectura::Validada,
            'motivo_rechazo' => $bloqueos
                ? implode(' · ', array_map(fn ($h) => "{$h->regla} {$h->mensaje}", $bloqueos))
                : null,
        ]);

        return redirect()->route('arquitecturas.show', [$proyecto, $arquitectura])
                         ->with($bloqueos ? 'error' : 'exito',
                             $bloqueos
                                 ? 'Arquitectura guardada como RECHAZADA: revisa los motivos.'
                                 : 'Arquitectura guardada y validada.');
    }

    public function show(Proyecto $proyecto, Arquitectura $arquitectura, MotorReglas $reglas, ConstructorEvaluable $constructor)
    {
        abort_unless($arquitectura->proyecto_id === $proyecto->id, 404);

        $arquitectura->load(['elementos.producto.tipo', 'elementos.producto.proveedor', 'elementos.region']);

        return view('arquitecturas.show', [
            'proyecto'     => $proyecto,
            'arquitectura' => $arquitectura,
            'hallazgos'    => $reglas->evaluar($constructor->desdeArquitectura($arquitectura)),
        ]);
    }
}
