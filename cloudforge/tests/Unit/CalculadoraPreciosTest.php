<?php

namespace Tests\Unit;

use App\Enums\Modalidad;
use App\Services\CalculadoraPrecios;
use PHPUnit\Framework\TestCase;

/**
 * T01 y T02: precios en modalidad mensual y por consumo (con el tope).
 * No usan base de datos, la calculadora solo hace cuentas.
 */
class CalculadoraPreciosTest extends TestCase
{
    private CalculadoraPrecios $calculadora;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculadora = new CalculadoraPrecios();
    }

    // T01: mensual = tarifa x cantidad
    public function test_t01_modalidad_mensual_calcula_el_importe_exacto(): void
    {
        $linea = $this->calculadora->linea(Modalidad::Mensual, 4550, 3);

        $this->assertSame(13650, $linea['importe_centimos']);   // 45,50 € x 3 = 136,50 €
        $this->assertTrue($linea['recurrente']);
    }

    // T01b: al sumar en centimos no hay fallos de redondeo
    public function test_t01_la_suma_de_lineas_coincide_con_el_total(): void
    {
        $lineas = [];

        for ($i = 0; $i < 7; $i++) {
            $lineas[] = $this->calculadora->linea(Modalidad::Mensual, 1033, 1);   // 10,33 €
        }

        $totales = $this->calculadora->totales($lineas);

        $this->assertSame(7231, $totales['mensual_centimos']);   // 72,31 €
        $this->assertSame('72,31 €', CalculadoraPrecios::euros($totales['mensual_centimos']));
    }

    // T02: por consumo se aplica el tope mensual
    public function test_t02_la_modalidad_por_consumo_respeta_el_tope_mensual(): void
    {
        // 0,12 € x 5000 GB = 600 €, no llega al tope de 900 €
        $sinTopar = $this->calculadora->linea(Modalidad::Consumo, 12, 1, 5000, 90000);
        $this->assertSame(60000, $sinTopar['importe_centimos']);

        // 0,12 € x 20000 GB = 2400 €, se pasa del tope asi que se queda en 900 €
        $topado = $this->calculadora->linea(Modalidad::Consumo, 12, 1, 20000, 90000);
        $this->assertSame(90000, $topado['importe_centimos']);
    }

    // T02b: el pago unico no se suma a lo mensual
    public function test_t02_separa_el_gasto_recurrente_del_pago_unico(): void
    {
        $mensual = $this->calculadora->linea(Modalidad::Mensual, 4550, 2);
        $unico   = $this->calculadora->linea(Modalidad::Unico, 24000, 1);

        $totales = $this->calculadora->totales([$mensual, $unico]);

        $this->assertSame(9100, $totales['mensual_centimos']);
        $this->assertSame(24000, $totales['unico_centimos']);
    }

    // si es por consumo y no se pasa el consumo estimado tiene que dar error
    public function test_la_modalidad_por_consumo_exige_consumo_estimado(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->calculadora->linea(Modalidad::Consumo, 12, 1, null, 90000);
    }
}
