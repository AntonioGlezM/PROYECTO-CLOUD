<?php

namespace App\Http\Controllers;

use App\Models\Presupuesto;
use App\Models\Proyecto;
use App\Services\EmisorPresupuestos;
use App\Services\PresupuestoBloqueado;

class PresupuestoController extends Controller
{
    public function index()
    {
        $presupuestos = Presupuesto::with('proyecto')
                                   ->withCount('lineas')
                                   ->latest('emitido_en')
                                   ->paginate(15);

        return view('presupuestos.index', compact('presupuestos'));
    }

    public function show(Presupuesto $presupuesto)
    {
        $presupuesto->load(['lineas', 'proyecto']);

        return view('presupuestos.show', compact('presupuesto'));
    }

    /** Emite el presupuesto del carrito del proyecto. */
    public function store(Proyecto $proyecto, EmisorPresupuestos $emisor)
    {
        try {
            $presupuesto = $emisor->emitir($proyecto);
        } catch (PresupuestoBloqueado $e) {
            // Las reglas que bloquean se muestran y no se escribe nada en la BD.
            return back()->with('error', 'No se ha emitido el presupuesto.')
                         ->with('bloqueos', $e->mensajes());
        }

        return redirect()->route('presupuestos.show', $presupuesto)
                         ->with('exito', "Presupuesto {$presupuesto->numero} emitido.");
    }
}
