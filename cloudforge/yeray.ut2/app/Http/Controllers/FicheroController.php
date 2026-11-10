<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubirFicheroRequest;
use App\Models\Auditoria;
use App\Models\Fichero;
use App\Models\Proyecto;
use Illuminate\Support\Facades\Storage;

class FicheroController extends Controller
{
    /**
     * Sube un documento y lo guarda en el proyecto.
     *
     * Primero se guarda el archivo y despues el registro en la BD. Si falla
     * la BD el archivo ya esta en el disco, y como Storage no va dentro de la
     * transaccion hay que borrarlo a mano.
     */
    public function store(SubirFicheroRequest $peticion, Proyecto $proyecto)
    {
        $subido = $peticion->file('documento');
        $ruta   = $subido->store("proyectos/{$proyecto->id}");

        try {
            $fichero = $proyecto->ficheros()->create([
                'nombre_original' => $subido->getClientOriginalName(),
                'ruta'            => $ruta,
                'mime'            => $subido->getClientMimeType(),
                'tamano'          => $subido->getSize(),
            ]);
        } catch (\Throwable $e) {
            Storage::delete($ruta);   // para que no se quede el archivo suelto

            throw $e;
        }

        Auditoria::registrar('subir_fichero', 'ficheros', $fichero->id, [
            'nombre' => $fichero->nombre_original,
        ]);

        return back()->with('exito', "Archivo «{$fichero->nombre_original}» subido.");
    }

    public function show(Proyecto $proyecto, Fichero $fichero)
    {
        abort_unless($fichero->proyecto_id === $proyecto->id, 404);
        abort_unless(Storage::exists($fichero->ruta), 404);

        return Storage::download($fichero->ruta, $fichero->nombre_original);
    }

    // cambia el archivo por uno nuevo, el registro se mantiene y el viejo se borra
    public function update(SubirFicheroRequest $peticion, Proyecto $proyecto, Fichero $fichero)
    {
        abort_unless($fichero->proyecto_id === $proyecto->id, 404);

        $anterior = $fichero->ruta;
        $subido   = $peticion->file('documento');
        $ruta     = $subido->store("proyectos/{$proyecto->id}");

        $fichero->update([
            'nombre_original' => $subido->getClientOriginalName(),
            'ruta'            => $ruta,
            'mime'            => $subido->getClientMimeType(),
            'tamano'          => $subido->getSize(),
        ]);

        Storage::delete($anterior);

        return back()->with('exito', 'Archivo sustituido.');
    }

    public function destroy(Proyecto $proyecto, Fichero $fichero)
    {
        abort_unless($fichero->proyecto_id === $proyecto->id, 404);

        Storage::delete($fichero->ruta);
        $fichero->delete();

        return back()->with('exito', 'Archivo eliminado.');
    }
}
