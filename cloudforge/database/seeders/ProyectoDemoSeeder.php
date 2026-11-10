<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Proyecto;
use App\Models\Region;
use App\Models\Requerimiento;
use Illuminate\Database\Seeder;

/**
 * Proyecto de ejemplo con el caso del enunciado: una academia con 500 alumnos
 * que necesita infraestructura y no sabe traducir eso en productos concretos.
 * Deja el carrito preparado para poder demostrar el recorrido completo.
 */
class ProyectoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $madrid = Region::where('codigo', 'eu-madrid')->firstOrFail();

        $proyecto = Proyecto::create([
            'nombre'                   => 'Campus virtual Academia Lumen',
            'cliente'                  => 'Academia Lumen S.L.',
            'descripcion'              => 'Plataforma de formación en línea para 500 alumnos '
                                        . 'con vídeo bajo demanda y datos alojados en España.',
            'presupuesto_max_centimos' => 40000,   // 400,00 € al mes
            'region_preferida_id'      => $madrid->id,
        ]);

        $requerimientos = [
            ['ram_min_gb',            '>=', '8',         'numero',   true,  'cliente'],
            ['cpu_min',               '>=', '4',         'numero',   true,  'cliente'],
            ['almacenamiento_min_gb', '>=', '200',       'numero',   true,  'cliente'],
            ['region',                'in', 'eu-madrid', 'lista',    true,  'cliente'],
            ['backup_requerido',      '=',  'si',        'booleano', false, 'equipo'],
        ];

        foreach ($requerimientos as [$clave, $op, $valor, $tipo, $oblig, $origen]) {
            Requerimiento::create([
                'proyecto_id' => $proyecto->id,
                'clave'       => $clave,
                'operador'    => $op,
                'valor'       => $valor,
                'tipo'        => $tipo,
                'obligatorio' => $oblig,
                'origen'      => $origen,
            ]);
        }

        // Carrito de partida: cumple los requisitos y cabe en el presupuesto.
        $lineas = [
            ['NB-C2', 1, null, ['vcpu' => 4, 'ram_gb' => 16]],
            ['NB-A1', 2, null, ['almacenamiento_gb' => 200]],
            ['NB-K1', 1, null, []],
        ];

        foreach ($lineas as [$sku, $cantidad, $consumo, $config]) {
            $producto = Producto::where('sku', $sku)->firstOrFail();

            $proyecto->lineasCarrito()->create([
                'producto_id'      => $producto->id,
                'region_id'        => $madrid->id,
                'cantidad'         => $cantidad,
                'consumo_estimado' => $consumo,
                'configuracion'    => $config,
            ]);
        }
    }
}
