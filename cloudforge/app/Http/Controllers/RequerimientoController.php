<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarRequerimientoRequest;
use App\Models\Proyecto;
use App\Models\Requerimiento;
use Illuminate\Http\Request;

class RequerimientoController extends Controller
{
    /**
     * Primer paso del formulario dinámico sin JavaScript: se elige la clave
     * y el servidor devuelve otra vista con los operadores y el campo de
     * valor que corresponden al tipo de esa clave.
     */
    public function create(Request $peticion, Proyecto $proyecto)
    {
        $datos = $peticion->validate([
            'clave' => ['nullable', 'string', 'in:' . implode(',', array_keys(Requerimiento::CLAVES))],
        ]);

        $clave = $datos['clave'] ?? null;

        return view('requerimientos.form', [
            'proyecto' => $proyecto,
            'clave'    => $clave,
            'tipo'     => $clave ? Requerimiento::TIPO_POR_CLAVE[$clave] : null,
        ]);
    }

    public function store(GuardarRequerimientoRequest $peticion, Proyecto $proyecto)
    {
        $proyecto->requerimientos()->create([
            ...$peticion->safe()->all(),
            'tipo'        => $peticion->tipoDeLaClave(),
            'obligatorio' => $peticion->boolean('obligatorio'),
        ]);

        return redirect()->route('proyectos.show', $proyecto)->with('exito', 'Requisito añadido.');
    }

    public function update(GuardarRequerimientoRequest $peticion, Proyecto $proyecto, Requerimiento $requerimiento)
    {
        abort_unless($requerimiento->proyecto_id === $proyecto->id, 404);

        $requerimiento->update([
            ...$peticion->safe()->all(),
            'tipo'        => $peticion->tipoDeLaClave(),
            'obligatorio' => $peticion->boolean('obligatorio'),
        ]);

        return redirect()->route('proyectos.show', $proyecto)->with('exito', 'Requisito actualizado.');
    }

    public function destroy(Proyecto $proyecto, Requerimiento $requerimiento)
    {
        abort_unless($requerimiento->proyecto_id === $proyecto->id, 404);

        $requerimiento->delete();

        return redirect()->route('proyectos.show', $proyecto)->with('exito', 'Requisito eliminado.');
    }
}
