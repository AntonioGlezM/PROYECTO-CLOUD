<?php

use App\Http\Controllers\ArquitecturaController;
use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\FicheroController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\RequerimientoController;
use Illuminate\Support\Facades\Route;

/*
 * Todas las rutas llevan a un controlador: ninguna resuelve lógica aquí.
 * Los nombres (catalogo.index, proyectos.show...) son los que usan las
 * vistas, para no escribir URLs a mano en Blade.
 */

Route::get('/', InicioController::class)->name('inicio');

// Vista didáctica: comparación entre PHP embebido y Blade, y escape de HTML.
Route::view('/diagnostico/escape', 'diagnostico.escape')->name('diagnostico.escape');

// --------------------------------------------------------------- Catálogo
Route::controller(CatalogoController::class)->prefix('catalogo')->name('catalogo.')->group(function () {
    Route::get('/',                 'index')->name('index');
    Route::get('/nuevo',            'create')->name('create');
    Route::post('/',                'store')->name('store');
    Route::get('/{producto}',       'show')->name('show');
    Route::get('/{producto}/editar', 'edit')->name('edit');
    Route::patch('/{producto}',     'update')->name('update');
    Route::delete('/{producto}',    'destroy')->name('destroy');
});

// --------------------------------------------------------------- Proyectos
Route::resource('proyectos', ProyectoController::class)->parameters(['proyectos' => 'proyecto']);

// Requisitos de un proyecto
Route::prefix('proyectos/{proyecto}/requerimientos')->name('requerimientos.')
    ->controller(RequerimientoController::class)->group(function () {
        Route::get('/nuevo',            'create')->name('create');
        Route::post('/',                'store')->name('store');
        Route::patch('/{requerimiento}', 'update')->name('update');
        Route::delete('/{requerimiento}', 'destroy')->name('destroy');
    });

// Selección / carrito
Route::prefix('proyectos/{proyecto}/carrito')->name('carrito.')
    ->controller(CarritoController::class)->group(function () {
        Route::get('/elegir',    'elegir')->name('elegir');
        Route::post('/',         'store')->name('store');
        Route::patch('/{linea}', 'update')->name('update');
        Route::delete('/{linea}', 'destroy')->name('destroy');
    });

// Arquitecturas guardadas
Route::prefix('proyectos/{proyecto}/arquitecturas')->name('arquitecturas.')
    ->controller(ArquitecturaController::class)->group(function () {
        Route::post('/',               'store')->name('store');
        Route::get('/{arquitectura}',  'show')->name('show');
    });

// Documentos adjuntos
Route::prefix('proyectos/{proyecto}/ficheros')->name('ficheros.')
    ->controller(FicheroController::class)->group(function () {
        Route::post('/',            'store')->name('store');
        Route::get('/{fichero}',    'show')->name('show');
        Route::post('/{fichero}',   'update')->name('update');
        Route::delete('/{fichero}', 'destroy')->name('destroy');
    });

// --------------------------------------------------------------- Presupuestos
Route::post('proyectos/{proyecto}/presupuestos', [PresupuestoController::class, 'store'])
     ->name('presupuestos.store');
Route::get('presupuestos', [PresupuestoController::class, 'index'])->name('presupuestos.index');
Route::get('presupuestos/{presupuesto}', [PresupuestoController::class, 'show'])->name('presupuestos.show');
