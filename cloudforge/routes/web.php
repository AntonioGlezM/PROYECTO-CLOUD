<?php

// ---------------------------------------------------------------------------
// Fragmento para routes/web.php del proyecto Laravel.
// Sustituye a la ruta de bienvenida que Laravel trae de serie.
// ---------------------------------------------------------------------------

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstadoController;

// La raíz delega en el controlador: aquí no hay lógica, solo la asociación
// entre una URL + método HTTP y la acción que responde.
Route::get('/', [EstadoController::class, 'index'])->name('estado');
