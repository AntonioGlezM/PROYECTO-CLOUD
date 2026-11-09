<?php

namespace Tests\Feature;

use App\Models\Proyecto;
use App\Models\Requerimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T05 y T06 · Un alta válida que se guarda y una inválida que NO se guarda.
 */
class RequerimientoTest extends TestCase
{
    use RefreshDatabase;

    private function proyecto(): Proyecto
    {
        return Proyecto::create([
            'nombre'                   => 'Proyecto de prueba',
            'cliente'                  => 'Cliente de prueba',
            'presupuesto_max_centimos' => 40000,
        ]);
    }

    /** T05 · Un requisito coherente se guarda y queda asociado al proyecto. */
    public function test_t05_un_requisito_valido_se_guarda(): void
    {
        $proyecto = $this->proyecto();

        $respuesta = $this->post(route('requerimientos.store', $proyecto), [
            'clave'       => 'ram_min_gb',
            'operador'    => '>=',
            'valor'       => '16',
            'obligatorio' => '1',
            'origen'      => 'cliente',
        ]);

        $respuesta->assertRedirect(route('proyectos.show', $proyecto));

        $this->assertDatabaseHas('requerimientos', [
            'proyecto_id' => $proyecto->id,
            'clave'       => 'ram_min_gb',
            'operador'    => '>=',
            'valor'       => '16',
            'tipo'        => 'numero',     // el tipo lo deduce el servidor, no el formulario
            'obligatorio' => true,
        ]);

        $this->assertSame(1, $proyecto->requerimientos()->count());
    }

    /**
     * T06 · Un operador incompatible con el tipo de la clave se rechaza y no
     * escribe nada. «region» es de tipo lista y sólo admite «in».
     */
    public function test_t06_un_operador_incompatible_no_se_guarda(): void
    {
        $proyecto = $this->proyecto();

        $respuesta = $this->from(route('requerimientos.create', $proyecto))
                          ->post(route('requerimientos.store', $proyecto), [
                              'clave'    => 'region',
                              'operador' => '>=',       // incompatible con el tipo lista
                              'valor'    => 'eu-madrid',
                              'origen'   => 'cliente',
                          ]);

        $respuesta->assertSessionHasErrors('operador');

        $this->assertDatabaseCount('requerimientos', 0);
        $this->assertSame(0, $proyecto->requerimientos()->count());
    }

    /** T06b · Un valor no numérico para una clave numérica tampoco se guarda. */
    public function test_t06_un_valor_no_numerico_en_clave_numerica_no_se_guarda(): void
    {
        $proyecto = $this->proyecto();

        $this->post(route('requerimientos.store', $proyecto), [
            'clave'    => 'ram_min_gb',
            'operador' => '>=',
            'valor'    => 'bastante',
            'origen'   => 'cliente',
        ])->assertSessionHasErrors('valor');

        $this->assertDatabaseCount('requerimientos', 0);
    }

    /** Los campos correctos se conservan al volver al formulario con error. */
    public function test_el_formulario_conserva_los_datos_validos_tras_un_error(): void
    {
        $proyecto = $this->proyecto();

        $this->post(route('requerimientos.store', $proyecto), [
            'clave'    => 'ram_min_gb',
            'operador' => '>=',
            'valor'    => 'bastante',
            'origen'   => 'cliente',
        ])->assertSessionHasInput('clave', 'ram_min_gb');
    }

    /** Borrar un requisito de otro proyecto devuelve 404. */
    public function test_no_se_puede_borrar_un_requisito_de_otro_proyecto(): void
    {
        $unProyecto  = $this->proyecto();
        $otroProyecto = $this->proyecto();

        $requisito = Requerimiento::create([
            'proyecto_id' => $unProyecto->id, 'clave' => 'cpu_min', 'operador' => '>=',
            'valor' => '4', 'tipo' => 'numero', 'obligatorio' => true, 'origen' => 'cliente',
        ]);

        $this->delete(route('requerimientos.destroy', [$otroProyecto, $requisito]))
             ->assertNotFound();

        $this->assertDatabaseCount('requerimientos', 1);
    }
}
