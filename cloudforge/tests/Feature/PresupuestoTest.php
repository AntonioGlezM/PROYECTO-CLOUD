<?php

namespace Tests\Feature;

use App\Models\Presupuesto;
use App\Models\Producto;
use App\Models\Proyecto;
use App\Models\Region;
use App\Services\CalculadoraPrecios;
use App\Services\EmisorPresupuestos;
use App\Services\PresupuestoBloqueado;
use Database\Seeders\CatalogoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T09 y T10 · Presupuesto histórico inmutable y transacción con reversión.
 */
class PresupuestoTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CatalogoSeeder::class);

        $madrid = Region::where('codigo', 'eu-madrid')->firstOrFail();

        $this->proyecto = Proyecto::create([
            'nombre'                   => 'Campus virtual',
            'cliente'                  => 'Academia Lumen',
            'presupuesto_max_centimos' => 40000,
            'region_preferida_id'      => $madrid->id,
        ]);

        // Selección válida: 3 elementos de 3 tipos distintos, dentro de presupuesto.
        foreach ([['NB-C2', 1, ['vcpu' => 4, 'ram_gb' => 16]],
                  ['NB-A1', 2, ['almacenamiento_gb' => 200]],
                  ['NB-K1', 1, []]] as [$sku, $cantidad, $config]) {
            $this->proyecto->lineasCarrito()->create([
                'producto_id'   => Producto::where('sku', $sku)->value('id'),
                'region_id'     => $madrid->id,
                'cantidad'      => $cantidad,
                'configuracion' => $config,
            ]);
        }
    }

    private function emisor(): EmisorPresupuestos
    {
        return app(EmisorPresupuestos::class);
    }

    public function test_emitir_calcula_los_totales_y_vacia_el_carrito(): void
    {
        $presupuesto = $this->emisor()->emitir($this->proyecto);

        // 45,50 + (11,50 x 2) + 18,00 = 86,50 €
        $this->assertSame(8650, $presupuesto->total_mensual_centimos);
        $this->assertSame(0, $presupuesto->total_unico_centimos);
        $this->assertCount(3, $presupuesto->lineas);
        $this->assertSame(0, $this->proyecto->lineasCarrito()->count());
        $this->assertStringStartsWith('PRE-', $presupuesto->numero);
    }

    /**
     * T09 · Después de emitir se cambia la tarifa y el nombre del producto en
     * el catálogo; el presupuesto ya emitido debe seguir igual.
     */
    public function test_t09_el_presupuesto_emitido_no_cambia_al_modificar_el_catalogo(): void
    {
        $presupuesto = $this->emisor()->emitir($this->proyecto);

        $totalAntes  = $presupuesto->total_mensual_centimos;
        $lineaAntes  = $presupuesto->lineas()->where('producto_sku', 'NB-C2')->firstOrFail();

        $this->assertSame(4550, $lineaAntes->tarifa_centimos);
        $this->assertSame('Instancia Estándar 4 vCPU', $lineaAntes->producto_nombre);

        // Cambia el catálogo: sube la tarifa un 60 % y se renombra el producto.
        Producto::where('sku', 'NB-C2')->update([
            'tarifa_centimos' => 7280,
            'nombre'          => 'Instancia Estándar 4 vCPU (edición 2027)',
        ]);

        $presupuesto->refresh()->load('lineas');
        $lineaDespues = $presupuesto->lineas()->where('producto_sku', 'NB-C2')->firstOrFail();

        $this->assertSame($totalAntes, $presupuesto->total_mensual_centimos);
        $this->assertSame(4550, $lineaDespues->tarifa_centimos);
        $this->assertSame('Instancia Estándar 4 vCPU', $lineaDespues->producto_nombre);
    }

    /** Una regla que bloquea impide emitir y no escribe nada. */
    public function test_una_regla_bloqueante_impide_emitir_y_no_guarda_nada(): void
    {
        // Se baja el presupuesto máximo por debajo del coste: salta R04.
        $this->proyecto->update(['presupuesto_max_centimos' => 1000]);

        try {
            $this->emisor()->emitir($this->proyecto->fresh());
            $this->fail('Debería haber lanzado PresupuestoBloqueado.');
        } catch (PresupuestoBloqueado $e) {
            $this->assertNotEmpty($e->mensajes());
        }

        $this->assertDatabaseCount('presupuestos', 0);
        $this->assertDatabaseCount('lineas_presupuesto', 0);
        $this->assertSame(3, $this->proyecto->lineasCarrito()->count());   // el carrito sigue intacto
    }

    public function test_un_carrito_vacio_no_emite_presupuesto(): void
    {
        $this->proyecto->lineasCarrito()->delete();

        $this->expectException(PresupuestoBloqueado::class);

        $this->emisor()->emitir($this->proyecto->fresh());
    }

    /**
     * T10 · Si falla una escritura a mitad de la emisión, la transacción
     * revierte: no queda ni la cabecera, ni las líneas ya escritas, ni el
     * carrito vaciado.
     *
     * Para provocar el fallo se sustituye la calculadora por una que revienta
     * a partir de cierta llamada. Las tres primeras corresponden a la
     * validación previa; la quinta cae ya dentro de la transacción, cuando
     * una línea se ha escrito.
     */
    public function test_t10_un_fallo_a_mitad_de_la_emision_revierte_toda_la_operacion(): void
    {
        // Se registra como instancia única para que el contador lo compartan
        // el constructor de la evaluación y el emisor; con bind() cada uno
        // recibiría un objeto distinto y el contador nunca llegaría a cinco.
        $calculadoraQueFalla = new class extends CalculadoraPrecios {
            private int $llamadas = 0;

            public function linea(
                \App\Enums\Modalidad $modalidad,
                int $tarifaCentimos,
                int $cantidad,
                ?int $consumoEstimado = null,
                ?int $topeMensualCentimos = null,
            ): array {
                if (++$this->llamadas === 5) {
                    throw new \RuntimeException('Fallo simulado durante la emisión.');
                }

                return parent::linea($modalidad, $tarifaCentimos, $cantidad, $consumoEstimado, $topeMensualCentimos);
            }
        };

        $this->app->instance(CalculadoraPrecios::class, $calculadoraQueFalla);

        try {
            app(EmisorPresupuestos::class)->emitir($this->proyecto);
            $this->fail('Debería haber lanzado la excepción simulada.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Fallo simulado durante la emisión.', $e->getMessage());
        }

        // Nada a medias: ni cabecera, ni líneas, y el carrito sigue completo.
        $this->assertDatabaseCount('presupuestos', 0);
        $this->assertDatabaseCount('lineas_presupuesto', 0);
        $this->assertSame(3, $this->proyecto->lineasCarrito()->count());
    }

    /** La numeración es correlativa dentro del mismo año. */
    public function test_los_numeros_de_presupuesto_son_correlativos(): void
    {
        $madrid = Region::where('codigo', 'eu-madrid')->firstOrFail();

        $primero = $this->emisor()->emitir($this->proyecto);

        foreach ([['NB-C2', 1, ['vcpu' => 4, 'ram_gb' => 16]],
                  ['NB-A1', 2, ['almacenamiento_gb' => 200]],
                  ['NB-K1', 1, []]] as [$sku, $cantidad, $config]) {
            $this->proyecto->lineasCarrito()->create([
                'producto_id'   => Producto::where('sku', $sku)->value('id'),
                'region_id'     => $madrid->id,
                'cantidad'      => $cantidad,
                'configuracion' => $config,
            ]);
        }

        $segundo = $this->emisor()->emitir($this->proyecto->fresh());

        $this->assertSame(2, Presupuesto::count());
        $this->assertNotSame($primero->numero, $segundo->numero);
    }
}
