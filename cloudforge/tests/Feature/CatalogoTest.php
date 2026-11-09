<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Region;
use Database\Seeders\CatalogoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T07 · Operación CRUD y relación Eloquent con datos reales del catálogo.
 */
class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogoSeeder::class);
    }

    /** El catálogo cumple los mínimos que exige el enunciado. */
    public function test_el_catalogo_cumple_los_minimos_exigidos(): void
    {
        $this->assertDatabaseCount('proveedores', 2);
        $this->assertDatabaseCount('tipos_producto', 6);
        $this->assertGreaterThanOrEqual(5, Region::count());
        $this->assertGreaterThanOrEqual(15, Producto::count());
    }

    /** T07 · Editar un producto cambia lo guardado en la base de datos. */
    public function test_t07_editar_un_producto_modifica_los_datos_guardados(): void
    {
        $producto = Producto::where('sku', 'NB-C2')->firstOrFail();

        $this->patch(route('catalogo.update', $producto), [
            'proveedor_id'     => $producto->proveedor_id,
            'tipo_producto_id' => $producto->tipo_producto_id,
            'nombre'           => 'Instancia Estándar 4 vCPU (revisada)',
            'sku'              => 'NB-C2',
            'modalidad'        => 'mensual',
            'tarifa'           => 49.90,
            'unidad'           => 'instancia/mes',
        ])->assertRedirect();

        $this->assertDatabaseHas('productos', [
            'id'              => $producto->id,
            'nombre'          => 'Instancia Estándar 4 vCPU (revisada)',
            'tarifa_centimos' => 4990,
        ]);
    }

    /** T07b · La relación N:M producto–región lleva datos propios en el pivote. */
    public function test_t07_la_relacion_producto_region_guarda_disponibilidad_y_tarifa(): void
    {
        $producto = Producto::with('regiones')->where('sku', 'NB-C1')->firstOrFail();

        $madrid   = $producto->regiones->firstWhere('codigo', 'eu-madrid');
        $virginia = $producto->regiones->firstWhere('codigo', 'us-east');

        $this->assertTrue((bool) $madrid->pivot->disponible);
        $this->assertNull($madrid->pivot->tarifa_centimos);            // usa la tarifa base
        $this->assertSame(2695, (int) $virginia->pivot->tarifa_centimos); // 2450 + 10 %
        $this->assertSame(2695, $producto->tarifaEnRegion($virginia->id));
        $this->assertSame(2450, $producto->tarifaEnRegion($madrid->id));
    }

    /** Hay al menos un producto que NO está disponible en alguna región. */
    public function test_existe_un_producto_no_disponible_en_alguna_region(): void
    {
        $cortafuegos = Producto::with('regiones')->where('sku', 'NB-S1')->firstOrFail();
        $paris       = $cortafuegos->regiones->firstWhere('codigo', 'eu-paris');

        $this->assertFalse((bool) $paris->pivot->disponible);
        $this->assertFalse($cortafuegos->estaDisponibleEn($paris->id));
    }

    /** El filtro por región sólo devuelve productos realmente disponibles. */
    public function test_el_filtro_por_region_excluye_lo_no_disponible(): void
    {
        $paris = Region::where('codigo', 'eu-paris')->firstOrFail();

        $encontrados = Producto::activos()->disponibleEnRegion($paris->id)->pluck('sku');

        $this->assertContains('NB-C2', $encontrados);
        $this->assertNotContains('NB-S1', $encontrados);
    }

    /** Retirar un producto lo desactiva en lugar de borrarlo. */
    public function test_retirar_un_producto_lo_desactiva_sin_borrarlo(): void
    {
        $producto = Producto::where('sku', 'NB-C3')->firstOrFail();

        $this->delete(route('catalogo.destroy', $producto))->assertRedirect();

        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'activo' => false]);
    }
}
