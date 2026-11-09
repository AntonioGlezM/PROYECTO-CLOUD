<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Proyecto;
use App\Models\Region;
use App\Models\TipoProducto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CarritoController extends Controller
{
    /**
     * Formulario dinámico en tres pasos SIN JavaScript. Cada paso se envía al
     * servidor por GET y Laravel devuelve la vista siguiente con los campos
     * que corresponden a lo elegido antes:
     *   1. tipo de servicio  →  2. producto y región  →  3. configuración
     */
    public function elegir(Request $peticion, Proyecto $proyecto)
    {
        $datos = $peticion->validate([
            'tipo_producto_id' => ['nullable', 'integer', 'exists:tipos_producto,id'],
            'producto_id'      => ['nullable', 'integer', 'exists:productos,id'],
        ]);

        $tipo     = isset($datos['tipo_producto_id']) ? TipoProducto::find($datos['tipo_producto_id']) : null;
        $producto = isset($datos['producto_id'])
            ? Producto::with(['regiones', 'tipo', 'proveedor'])->find($datos['producto_id'])
            : null;

        // Paso 2: productos de ese tipo que estén activos.
        $productos = $tipo
            ? Producto::activos()->with('proveedor')->deTipo($tipo->id)->orderBy('nombre')->get()
            : collect();

        return view('carrito.elegir', [
            'proyecto'  => $proyecto,
            'tipos'     => TipoProducto::orderBy('nombre')->get(),
            'tipo'      => $tipo,
            'productos' => $productos,
            'producto'  => $producto,
            // Sólo las regiones donde el producto está realmente disponible.
            'regiones'  => $producto
                ? $producto->regiones->filter(fn ($r) => $r->pivot->disponible)
                : collect(),
        ]);
    }

    public function store(Request $peticion, Proyecto $proyecto)
    {
        $datos = $peticion->validate([
            'producto_id'       => ['required', 'integer', 'exists:productos,id'],
            'region_id'         => ['required', 'integer', 'exists:regiones,id'],
            'cantidad'          => ['required', 'integer', 'min:1', 'max:500'],
            'consumo_estimado'  => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'vcpu'              => ['nullable', 'integer', 'min:0', 'max:256'],
            'ram_gb'            => ['nullable', 'integer', 'min:0', 'max:4096'],
            'almacenamiento_gb' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $producto = Producto::with('regiones')->findOrFail($datos['producto_id']);

        // Comprobación en el servidor: aunque alguien manipule el formulario,
        // no se puede meter un producto en una región donde no se ofrece.
        if (! $producto->estaDisponibleEn($datos['region_id'])) {
            return back()->withInput()->with('error',
                "«{$producto->nombre}» no está disponible en esa región.");
        }

        if ($producto->modalidad->value === 'consumo' && ! isset($datos['consumo_estimado'])) {
            return back()->withInput()->withErrors([
                'consumo_estimado' => 'Este producto se factura por consumo: indica el consumo estimado al mes.',
            ]);
        }

        $configuracion = array_filter([
            'vcpu'              => $datos['vcpu'] ?? null,
            'ram_gb'            => $datos['ram_gb'] ?? null,
            'almacenamiento_gb' => $datos['almacenamiento_gb'] ?? null,
        ], fn ($v) => $v !== null);

        // Si ya estaba la misma combinación producto+región, se suma cantidad.
        $linea = $proyecto->lineasCarrito()
                          ->where('producto_id', $producto->id)
                          ->where('region_id', $datos['region_id'])
                          ->first();

        if ($linea) {
            $linea->update([
                'cantidad'         => $linea->cantidad + $datos['cantidad'],
                'consumo_estimado' => $datos['consumo_estimado'] ?? $linea->consumo_estimado,
                'configuracion'    => $configuracion ?: $linea->configuracion,
            ]);
        } else {
            $proyecto->lineasCarrito()->create([
                'producto_id'      => $producto->id,
                'region_id'        => $datos['region_id'],
                'cantidad'         => $datos['cantidad'],
                'consumo_estimado' => $datos['consumo_estimado'] ?? null,
                'configuracion'    => $configuracion,
            ]);
        }

        return redirect()->route('proyectos.show', $proyecto)
                         ->with('exito', "«{$producto->nombre}» añadido a la selección.");
    }

    public function update(Request $peticion, Proyecto $proyecto, int $linea)
    {
        $fila = $proyecto->lineasCarrito()->findOrFail($linea);

        $datos = $peticion->validate([
            'cantidad'         => ['required', 'integer', 'min:1', 'max:500'],
            'consumo_estimado' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $fila->update($datos);

        return redirect()->route('proyectos.show', $proyecto)->with('exito', 'Línea actualizada.');
    }

    public function destroy(Proyecto $proyecto, int $linea)
    {
        $proyecto->lineasCarrito()->findOrFail($linea)->delete();

        return redirect()->route('proyectos.show', $proyecto)->with('exito', 'Línea retirada.');
    }
}
