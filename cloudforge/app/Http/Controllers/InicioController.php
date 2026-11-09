<?php

namespace App\Http\Controllers;

use App\Models\Presupuesto;
use App\Models\Producto;
use App\Models\Proyecto;

class InicioController extends Controller
{
    public function __invoke()
    {
        return view('inicio', [
            'totalProductos'   => Producto::activos()->count(),
            'totalProyectos'   => Proyecto::count(),
            'totalPresupuestos' => Presupuesto::count(),
            // withCount evita una consulta por proyecto al pintar la lista.
            'ultimosProyectos' => Proyecto::withCount(['requerimientos', 'presupuestos'])
                                          ->latest()->take(5)->get(),
        ]);
    }
}
