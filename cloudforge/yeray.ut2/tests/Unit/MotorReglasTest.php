<?php

namespace Tests\Unit;

use App\Services\ArquitecturaEvaluable;
use App\Services\MotorReglas;
use PHPUnit\Framework\TestCase;

/**
 * T03 y T04: reglas que bloquean el presupuesto y reglas que solo avisan.
 */
class MotorReglasTest extends TestCase
{
    private MotorReglas $motor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->motor = new MotorReglas();
    }

    // arquitectura que esta bien (3 elementos, 3 tipos, 2 regiones), en cada test se cambia lo que haga falta
    private function arquitecturaValida(array $cambios = []): ArquitecturaEvaluable
    {
        return new ArquitecturaEvaluable(
            elementos: $cambios['elementos'] ?? [
                ['nombre' => 'Instancia Estándar', 'tipo' => 'computo',
                 'region' => 'eu-madrid', 'disponible' => true],
                ['nombre' => 'Disco SSD', 'tipo' => 'almacenamiento',
                 'region' => 'eu-madrid', 'disponible' => true],
                ['nombre' => 'Copia diaria', 'tipo' => 'backup',
                 'region' => 'eu-paris', 'disponible' => true],
            ],
            requerimientos: $cambios['requerimientos'] ?? [],
            hechos: $cambios['hechos'] ?? ['ram_min_gb' => 32],
            totalMensualCentimos: $cambios['total'] ?? 8650,
            presupuestoMaxCentimos: $cambios['maximo'] ?? 40000,
        );
    }

    private function reglas(array $hallazgos): array
    {
        return array_map(fn ($h) => $h->regla, $hallazgos);
    }

    public function test_una_arquitectura_correcta_no_produce_ningun_hallazgo(): void
    {
        $hallazgos = $this->motor->evaluar($this->arquitecturaValida());

        $this->assertSame([], $hallazgos);
        $this->assertFalse($this->motor->hayBloqueo($hallazgos));
    }

    // T03: R04 bloquea si se pasa del presupuesto
    public function test_t03_superar_el_presupuesto_bloquea_la_emision(): void
    {
        $hallazgos = $this->motor->evaluar(
            $this->arquitecturaValida(['total' => 150000, 'maximo' => 100000])
        );

        $this->assertContains('R04', $this->reglas($hallazgos));
        $this->assertTrue($this->motor->hayBloqueo($hallazgos));

        $r04 = array_values(array_filter($hallazgos, fn ($h) => $h->regla === 'R04'))[0];
        $this->assertSame(50000, $r04->detalle['exceso_centimos']);
    }

    // T03b: si es justo el limite si se puede emitir
    public function test_t03_el_importe_exacto_del_limite_no_bloquea(): void
    {
        $hallazgos = $this->motor->evaluar(
            $this->arquitecturaValida(['total' => 100000, 'maximo' => 100000])
        );

        $this->assertNotContains('R04', $this->reglas($hallazgos));
    }

    // T03c: R01 bloquea si un producto no esta disponible en su region
    public function test_t03_un_producto_no_disponible_en_su_region_bloquea(): void
    {
        $elementos = [
            ['nombre' => 'Instancia Intensiva', 'tipo' => 'computo',
             'region' => 'eu-madrid', 'disponible' => false],
            ['nombre' => 'Disco SSD', 'tipo' => 'almacenamiento',
             'region' => 'eu-madrid', 'disponible' => true],
            ['nombre' => 'Copia diaria', 'tipo' => 'backup',
             'region' => 'eu-paris', 'disponible' => true],
        ];

        $hallazgos = $this->motor->evaluar($this->arquitecturaValida(['elementos' => $elementos]));

        $this->assertContains('R01', $this->reglas($hallazgos));
        $this->assertTrue($this->motor->hayBloqueo($hallazgos));
    }

    // T04: R05 avisa si no hay backup pero deja emitir
    public function test_t04_la_falta_de_copia_de_seguridad_avisa_sin_bloquear(): void
    {
        $elementos = [
            ['nombre' => 'Instancia', 'tipo' => 'computo', 'region' => 'eu-madrid', 'disponible' => true],
            ['nombre' => 'Disco SSD', 'tipo' => 'almacenamiento', 'region' => 'eu-madrid', 'disponible' => true],
            ['nombre' => 'Balanceador', 'tipo' => 'red', 'region' => 'eu-paris', 'disponible' => true],
        ];

        $hallazgos = $this->motor->evaluar($this->arquitecturaValida(['elementos' => $elementos]));

        $this->assertContains('R05', $this->reglas($hallazgos));
        $this->assertFalse($this->motor->hayBloqueo($hallazgos));
    }

    // T04b: R06 avisa si todo esta en la misma region
    public function test_t04_una_sola_region_avisa_sin_bloquear(): void
    {
        $elementos = [
            ['nombre' => 'Instancia', 'tipo' => 'computo', 'region' => 'eu-madrid', 'disponible' => true],
            ['nombre' => 'Disco SSD', 'tipo' => 'almacenamiento', 'region' => 'eu-madrid', 'disponible' => true],
            ['nombre' => 'Copia diaria', 'tipo' => 'backup', 'region' => 'eu-madrid', 'disponible' => true],
        ];

        $hallazgos = $this->motor->evaluar($this->arquitecturaValida(['elementos' => $elementos]));

        $this->assertContains('R06', $this->reglas($hallazgos));
        $this->assertFalse($this->motor->hayBloqueo($hallazgos));
    }

    // un requisito obligatorio que no se cumple bloquea, una preferencia no
    public function test_r02_distingue_requisito_obligatorio_de_preferencia(): void
    {
        $obligatorio = [['clave' => 'ram_min_gb', 'operador' => '>=', 'valor' => '64',
                         'tipo' => 'numero', 'obligatorio' => true]];
        $preferencia = [['clave' => 'ram_min_gb', 'operador' => '>=', 'valor' => '64',
                         'tipo' => 'numero', 'obligatorio' => false]];

        $conObligatorio = $this->motor->evaluar(
            $this->arquitecturaValida(['requerimientos' => $obligatorio, 'hechos' => ['ram_min_gb' => 16]])
        );
        $conPreferencia = $this->motor->evaluar(
            $this->arquitecturaValida(['requerimientos' => $preferencia, 'hechos' => ['ram_min_gb' => 16]])
        );

        $this->assertTrue($this->motor->hayBloqueo($conObligatorio));
        $this->assertFalse($this->motor->hayBloqueo($conPreferencia));
    }

    // usar un operador que no va con el tipo tiene que lanzar excepcion, no devolver false
    public function test_un_operador_incompatible_con_el_tipo_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->motor->cumple('madrid', '>=', '3', 'texto');
    }
}
