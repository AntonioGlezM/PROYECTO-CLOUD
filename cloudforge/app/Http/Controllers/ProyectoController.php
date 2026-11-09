<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarProyectoRequest;
use App\Models\Auditoria;
use App\Models\Proyecto;
use App\Models\Region;
use App\Services\ConstructorEvaluable;
use App\Services\MotorReglas;

class ProyectoController extends Controller
{
    public function index()
    {
        $proyectos = Proyecto::with('regionPreferida')
                             ->withCount(['requerimientos', 'lineasCarrito', 'presupuestos'])
                             ->orderByDesc('id')
                             ->paginate(10);

        return view('proyectos.index', compact('proyectos'));
    }

    public function show(Proyecto $proyecto, ConstructorEvaluable $constructor, MotorReglas $reglas)
    {
        $proyecto->load([
            'regionPreferida', 'requerimientos', 'ficheros',
            'presupuestos' => fn ($q) => $q->latest('emitido_en'),
            'lineasCarrito.producto.tipo', 'lineasCarrito.region',
        ]);

        // Se evalúan las reglas sobre el carrito actual para avisar antes de
        // intentar emitir. El cálculo vive en el servidor, no en la vista.
        $evaluable = $constructor->desdeCarrito($proyecto);
        $hallazgos = $proyecto->lineasCarrito->isEmpty() ? [] : $reglas->evaluar($evaluable);

        return view('proyectos.show', [
            'proyecto'  => $proyecto,
            'evaluable' => $evaluable,
            'hallazgos' => $hallazgos,
            'bloqueado' => $reglas->hayBloqueo($hallazgos),
        ]);
    }

    public function create()
    {
        return view('proyectos.form', [
            'proyecto' => new Proyecto(),
            'regiones' => Region::with('proveedor')->orderBy('nombre')->get(),
        ]);
    }

    public function store(GuardarProyectoRequest $peticion)
    {
        $proyecto = Proyecto::create([
            ...$peticion->safe()->except('presupuesto_max'),
            'presupuesto_max_centimos' => $peticion->presupuestoCentimos(),
        ]);

        Auditoria::registrar('crear_proyecto', 'proyectos', $proyecto->id);

        return redirect()->route('proyectos.show', $proyecto)
                         ->with('exito', 'Proyecto creado. Ahora añade sus requisitos.');
    }

    public function edit(Proyecto $proyecto)
    {
        return view('proyectos.form', [
            'proyecto' => $proyecto,
            'regiones' => Region::with('proveedor')->orderBy('nombre')->get(),
        ]);
    }

    public function update(GuardarProyectoRequest $peticion, Proyecto $proyecto)
    {
        $proyecto->update([
            ...$peticion->safe()->except('presupuesto_max'),
            'presupuesto_max_centimos' => $peticion->presupuestoCentimos(),
        ]);

        return redirect()->route('proyectos.show', $proyecto)->with('exito', 'Proyecto actualizado.');
    }

    /**
     * Sólo se borra si no tiene presupuestos emitidos: un presupuesto es un
     * documento histórico y no puede quedarse sin proyecto. Los requisitos,
     * arquitecturas y líneas de carrito sí caen en cascada con él.
     */
    public function destroy(Proyecto $proyecto)
    {
        if (! $proyecto->sePuedeBorrar()) {
            return back()->with('error',
                'No se puede borrar: el proyecto tiene presupuestos emitidos. '
                . 'Los presupuestos son documentos históricos y deben conservarse.');
        }

        $nombre = $proyecto->nombre;
        $proyecto->delete();

        Auditoria::registrar('borrar_proyecto', 'proyectos', null, ['nombre' => $nombre]);

        return redirect()->route('proyectos.index')->with('exito', "Proyecto «{$nombre}» borrado.");
    }
}
