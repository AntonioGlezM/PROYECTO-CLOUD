<?php

namespace App\Http\Controllers;

use App\Enums\Modalidad;
use App\Http\Requests\GuardarProductoRequest;
use App\Models\Auditoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Region;
use App\Models\TipoProducto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /** Listado con filtros por GET y paginación. */
    public function index(Request $peticion)
    {
        // Los filtros se validan: un tipo inexistente no debe reventar la consulta.
        $filtros = $peticion->validate([
            'buscar'       => ['nullable', 'string', 'max:60'],
            'tipo'         => ['nullable', 'integer', 'exists:tipos_producto,id'],
            'proveedor'    => ['nullable', 'integer', 'exists:proveedores,id'],
            'region'       => ['nullable', 'integer', 'exists:regiones,id'],
        ]);

        $productos = Producto::query()
            // Eager loading: sin esto el listado haría una consulta por producto
            // para mostrar su tipo y su proveedor (problema N+1).
            ->with(['tipo', 'proveedor'])
            ->activos()
            ->buscar($filtros['buscar'] ?? null)
            ->deTipo($filtros['tipo'] ?? null)
            ->deProveedor($filtros['proveedor'] ?? null)
            ->disponibleEnRegion($filtros['region'] ?? null)
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('catalogo.index', [
            'productos'   => $productos,
            'tipos'       => TipoProducto::orderBy('nombre')->get(),
            'proveedores' => Proveedor::orderBy('nombre')->get(),
            'regiones'    => Region::with('proveedor')->orderBy('nombre')->get(),
            'filtros'     => $filtros,
        ]);
    }

    public function show(Producto $producto)
    {
        $producto->load(['tipo', 'proveedor', 'regiones']);

        return view('catalogo.show', compact('producto'));
    }

    public function create()
    {
        return view('catalogo.form', [
            'producto'    => new Producto(['modalidad' => Modalidad::Mensual]),
            'tipos'       => TipoProducto::orderBy('nombre')->get(),
            'proveedores' => Proveedor::orderBy('nombre')->get(),
        ]);
    }

    public function store(GuardarProductoRequest $peticion)
    {
        $producto = Producto::create($this->datos($peticion));

        Auditoria::registrar('crear_producto', 'productos', $producto->id, ['sku' => $producto->sku]);

        return redirect()->route('catalogo.show', $producto)
                         ->with('exito', "Producto «{$producto->nombre}» creado.");
    }

    public function edit(Producto $producto)
    {
        return view('catalogo.form', [
            'producto'    => $producto,
            'tipos'       => TipoProducto::orderBy('nombre')->get(),
            'proveedores' => Proveedor::orderBy('nombre')->get(),
        ]);
    }

    public function update(GuardarProductoRequest $peticion, Producto $producto)
    {
        $tarifaAnterior = $producto->tarifa_centimos;
        $producto->update($this->datos($peticion));

        Auditoria::registrar('editar_producto', 'productos', $producto->id, [
            'tarifa_anterior' => $tarifaAnterior,
            'tarifa_nueva'    => $producto->tarifa_centimos,
        ]);

        return redirect()->route('catalogo.show', $producto)
                         ->with('exito', 'Producto actualizado. Los presupuestos ya emitidos no cambian.');
    }

    /**
     * No se borra: se desactiva. Un producto puede estar referenciado por
     * arquitecturas y por líneas de carrito, y borrarlo dejaría huecos.
     */
    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);

        Auditoria::registrar('desactivar_producto', 'productos', $producto->id);

        return redirect()->route('catalogo.index')
                         ->with('exito', "«{$producto->nombre}» se ha retirado del catálogo.");
    }

    /** Pasa los euros del formulario a céntimos. */
    private function datos(GuardarProductoRequest $peticion): array
    {
        $datos = $peticion->safe()->except(['tarifa', 'tope_mensual']);

        $datos['tarifa_centimos'] = (int) round(((float) $peticion->input('tarifa')) * 100);
        $datos['tope_mensual_centimos'] = $peticion->filled('tope_mensual')
            ? (int) round(((float) $peticion->input('tope_mensual')) * 100)
            : null;
        $datos['activo'] = $peticion->boolean('activo', true);

        return $datos;
    }
}
