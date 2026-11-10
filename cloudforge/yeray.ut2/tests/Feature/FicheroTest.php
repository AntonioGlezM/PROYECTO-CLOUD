<?php

namespace Tests\Feature;

use App\Models\Proyecto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * T08: subir, cambiar y rechazar ficheros (con Storage falso).
 */
class FicheroTest extends TestCase
{
    use RefreshDatabase;

    private Proyecto $proyecto;

    protected function setUp(): void
    {
        parent::setUp();

        // disco falso para que los tests no guarden nada de verdad
        Storage::fake();

        $this->proyecto = Proyecto::create([
            'nombre'                   => 'Proyecto con documentación',
            'cliente'                  => 'Cliente de prueba',
            'presupuesto_max_centimos' => 40000,
        ]);
    }

    // T08: un archivo valido se guarda en el disco y en la BD
    public function test_t08_un_archivo_valido_se_guarda_en_disco_y_en_la_base_de_datos(): void
    {
        $archivo = UploadedFile::fake()->create('pliego.pdf', 120, 'application/pdf');

        $this->post(route('ficheros.store', $this->proyecto), ['documento' => $archivo])
             ->assertRedirect();

        $this->assertDatabaseCount('ficheros', 1);

        $fichero = $this->proyecto->ficheros()->first();
        $this->assertSame('pliego.pdf', $fichero->nombre_original);
        Storage::assertExists($fichero->ruta);
    }

    // T08b: un tipo que no esta permitido se rechaza y no se guarda nada
    public function test_t08_un_archivo_de_tipo_no_admitido_se_rechaza(): void
    {
        $archivo = UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload');

        $this->post(route('ficheros.store', $this->proyecto), ['documento' => $archivo])
             ->assertSessionHasErrors('documento');

        $this->assertDatabaseCount('ficheros', 0);
        $this->assertCount(0, Storage::allFiles());
    }

    // T08c: un archivo de mas de 2 MB se rechaza
    public function test_t08_un_archivo_mayor_de_2mb_se_rechaza(): void
    {
        $archivo = UploadedFile::fake()->create('plano.pdf', 3000, 'application/pdf');   // 3 MB

        $this->post(route('ficheros.store', $this->proyecto), ['documento' => $archivo])
             ->assertSessionHasErrors('documento');

        $this->assertDatabaseCount('ficheros', 0);
    }

    // T08d: al cambiar el archivo se borra el anterior
    public function test_t08_sustituir_un_archivo_borra_el_anterior(): void
    {
        $this->post(route('ficheros.store', $this->proyecto), [
            'documento' => UploadedFile::fake()->create('v1.pdf', 50, 'application/pdf'),
        ]);

        $fichero = $this->proyecto->ficheros()->first();
        $rutaVieja = $fichero->ruta;

        $this->post(route('ficheros.update', [$this->proyecto, $fichero]), [
            'documento' => UploadedFile::fake()->create('v2.pdf', 60, 'application/pdf'),
        ])->assertRedirect();

        $fichero->refresh();

        $this->assertSame('v2.pdf', $fichero->nombre_original);
        Storage::assertMissing($rutaVieja);
        Storage::assertExists($fichero->ruta);
        $this->assertDatabaseCount('ficheros', 1);   // tiene que seguir habiendo uno solo
        $this->assertCount(1, Storage::allFiles());
    }

    // si se borra el registro tambien se borra el archivo
    public function test_borrar_el_registro_borra_el_archivo_del_disco(): void
    {
        $this->post(route('ficheros.store', $this->proyecto), [
            'documento' => UploadedFile::fake()->create('temporal.txt', 5, 'text/plain'),
        ]);

        $fichero = $this->proyecto->ficheros()->first();
        $ruta    = $fichero->ruta;

        $this->delete(route('ficheros.destroy', [$this->proyecto, $fichero]))->assertRedirect();

        $this->assertDatabaseCount('ficheros', 0);
        Storage::assertMissing($ruta);
    }
}
