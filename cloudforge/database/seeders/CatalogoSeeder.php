<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Region;
use App\Models\TipoProducto;
use Illuminate\Database\Seeder;

/**
 * Catálogo de partida. Cubre los mínimos del enunciado:
 * 2 proveedores, 6 regiones, 6 tipos y 15 productos, con productos
 * disponibles en varias regiones y alguno no disponible en otras.
 *
 * Los proveedores son ficticios a propósito: la aplicación simula la
 * contratación, no consulta precios reales de ningún proveedor.
 */
class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $proveedores = [
            'nubora' => Proveedor::create(['nombre' => 'Nubora Cloud', 'codigo' => 'nubora']),
            'vantia' => Proveedor::create(['nombre' => 'Vantia Systems', 'codigo' => 'vantia']),
        ];

        $regiones = [];
        $filasRegion = [
            ['nubora', 'Madrid',    'eu-madrid',    'España'],
            ['nubora', 'París',     'eu-paris',     'Francia'],
            ['nubora', 'Virginia',  'us-east',      'Estados Unidos'],
            ['vantia', 'Fráncfort', 'eu-frankfurt', 'Alemania'],
            ['vantia', 'Dublín',    'eu-dublin',    'Irlanda'],
            ['vantia', 'Canarias',  'eu-canarias',  'España'],
        ];

        foreach ($filasRegion as [$prov, $nombre, $codigo, $pais]) {
            $regiones[$codigo] = Region::create([
                'proveedor_id' => $proveedores[$prov]->id,
                'nombre'       => $nombre,
                'codigo'       => $codigo,
                'pais'         => $pais,
            ]);
        }

        $tipos = [];
        $filasTipo = [
            ['Cómputo', 'computo'],
            ['Almacenamiento', 'almacenamiento'],
            ['Base de datos', 'bbdd'],
            ['Red', 'red'],
            ['Seguridad', 'seguridad'],
            ['Copia de seguridad', 'backup'],
        ];

        foreach ($filasTipo as [$nombre, $codigo]) {
            $tipos[$codigo] = TipoProducto::create(['nombre' => $nombre, 'codigo' => $codigo]);
        }

        // sku, proveedor, tipo, nombre, modalidad, tarifa(cent), unidad, tope(cent), regiones disponibles
        $filasProducto = [
            ['NB-C1', 'nubora', 'computo', 'Instancia Básica 2 vCPU',      'mensual', 2450,  'instancia/mes', null,   ['eu-madrid', 'eu-paris', 'us-east']],
            ['NB-C2', 'nubora', 'computo', 'Instancia Estándar 4 vCPU',    'mensual', 4550,  'instancia/mes', null,   ['eu-madrid', 'eu-paris']],
            ['NB-C3', 'nubora', 'computo', 'Instancia Intensiva 16 vCPU',  'mensual', 18900, 'instancia/mes', null,   ['eu-paris', 'us-east']],
            ['NB-A1', 'nubora', 'almacenamiento', 'Disco SSD',             'mensual', 1150,  'bloque 100GB/mes', null, ['eu-madrid', 'eu-paris', 'us-east']],
            ['NB-A2', 'nubora', 'almacenamiento', 'Almacén de objetos',    'consumo', 12,    'GB almacenado', 90000,  ['eu-madrid', 'us-east']],
            ['NB-B1', 'nubora', 'bbdd', 'Base de datos gestionada',        'mensual', 7900,  'instancia/mes', null,   ['eu-madrid', 'eu-paris']],
            ['NB-R1', 'nubora', 'red', 'Balanceador de carga',             'mensual', 2200,  'balanceador/mes', null, ['eu-madrid', 'eu-paris', 'us-east']],
            ['NB-R2', 'nubora', 'red', 'Tránsito de datos saliente',       'consumo', 8,     'GB transferido', 45000, ['eu-madrid', 'eu-paris', 'us-east']],
            ['NB-S1', 'nubora', 'seguridad', 'Cortafuegos gestionado',     'mensual', 3400,  'instancia/mes', null,   ['eu-madrid']],
            ['NB-K1', 'nubora', 'backup', 'Copia diaria gestionada',       'mensual', 1800,  'servicio/mes', null,    ['eu-madrid', 'eu-paris']],
            ['VT-C1', 'vantia', 'computo', 'Nodo Compacto 2 vCPU',         'mensual', 2290,  'nodo/mes', null,        ['eu-frankfurt', 'eu-dublin', 'eu-canarias']],
            ['VT-C2', 'vantia', 'computo', 'Nodo Amplio 8 vCPU',           'mensual', 8700,  'nodo/mes', null,        ['eu-frankfurt', 'eu-dublin']],
            ['VT-A1', 'vantia', 'almacenamiento', 'Volumen replicado',     'mensual', 1600,  'bloque 100GB/mes', null, ['eu-frankfurt', 'eu-canarias']],
            ['VT-S1', 'vantia', 'seguridad', 'Auditoría de accesos',       'unico',   24000, 'implantación', null,    ['eu-frankfurt', 'eu-dublin', 'eu-canarias']],
            ['VT-K1', 'vantia', 'backup', 'Archivado a largo plazo',       'consumo', 4,     'GB archivado', 30000,   ['eu-frankfurt', 'eu-canarias']],
        ];

        foreach ($filasProducto as [$sku, $prov, $tipo, $nombre, $modalidad, $tarifa, $unidad, $tope, $disponibles]) {
            $producto = Producto::create([
                'proveedor_id'          => $proveedores[$prov]->id,
                'tipo_producto_id'      => $tipos[$tipo]->id,
                'nombre'                => $nombre,
                'sku'                   => $sku,
                'descripcion'           => "{$nombre} de {$proveedores[$prov]->nombre}.",
                'modalidad'             => $modalidad,
                'tarifa_centimos'       => $tarifa,
                'unidad'                => $unidad,
                'tope_mensual_centimos' => $tope,
            ]);

            // Se registra la relación con TODAS las regiones del proveedor:
            // las que no están en la lista quedan marcadas como no disponibles,
            // para poder demostrar el caso negativo (regla R01).
            foreach ($producto->proveedor->regiones as $region) {
                $disponible = in_array($region->codigo, $disponibles, true);

                $producto->regiones()->attach($region->id, [
                    'disponible'      => $disponible,
                    // Fuera de la zona euro la tarifa cambia: un 10 % más.
                    'tarifa_centimos' => $region->codigo === 'us-east'
                        ? (int) round($tarifa * 1.10)
                        : null,
                ]);
            }
        }
    }
}
