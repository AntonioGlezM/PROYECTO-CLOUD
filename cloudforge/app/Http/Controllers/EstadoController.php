<?php

namespace App\Http\Controllers;

/**
 * Página de comprobación del entorno (UT1).
 *
 * Su único objetivo es demostrar el ciclo MVC: la ruta delega aquí, aquí se
 * reúnen los datos y se entregan a una vista Blade que genera el HTML.
 * Todavía no interviene ningún modelo porque no se consulta la base de datos.
 */
class EstadoController extends Controller
{
    // Reúne los datos del entorno y se los pasa a la vista 'estado'.
    public function index()
    {
        return view('estado', [
            'nombreApp' => config('app.name'),   // Leído del .env (APP_NAME)
            'laravel'   => app()->version(),     // Versión del framework en ejecución
            'php'       => PHP_VERSION,          // Constante del propio intérprete
            'equipo'    => ['Antonio', 'Alejandro', 'Yassine', 'Yeray'],
        ]);
    }
}