<?php

namespace App\Services;

use App\Enums\Severidad;
use App\Enums\TipoDato;

/**
 * Reglas de negocio de CloudForge.
 *
 * R01 a R04 son errores: si salta alguna no se puede emitir el presupuesto.
 * R05 y R06 son avisos: se puede emitir igual, pero es algo que el cliente
 * deberia saber.
 */
class MotorReglas
{
    /** @return Hallazgo[] */
    public function evaluar(ArquitecturaEvaluable $a): array
    {
        return array_merge(
            $this->r01Disponibilidad($a),
            $this->r02RequisitosObligatorios($a),
            $this->r03ComposicionMinima($a),
            $this->r04PresupuestoMaximo($a),
            $this->r05CopiaSeguridad($a),
            $this->r06RedundanciaGeografica($a),
        );
    }

    /** @param Hallazgo[] $hallazgos */
    public function hayBloqueo(array $hallazgos): bool
    {
        foreach ($hallazgos as $h) {
            if ($h->bloquea()) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------------------------------- R01

    // R01: un producto no puede ir en una region donde no esta disponible
    private function r01Disponibilidad(ArquitecturaEvaluable $a): array
    {
        $hallazgos = [];

        foreach ($a->elementos as $e) {
            if (! $e['disponible']) {
                $hallazgos[] = new Hallazgo(
                    'R01',
                    Severidad::Error,
                    "El producto «{$e['nombre']}» no está disponible en la región {$e['region']}.",
                    ['producto' => $e['nombre'], 'region' => $e['region']],
                );
            }
        }

        return $hallazgos;
    }

    // ---------------------------------------------------------------- R02

    // R02: los requisitos obligatorios se tienen que cumplir
    private function r02RequisitosObligatorios(ArquitecturaEvaluable $a): array
    {
        $hallazgos = [];

        foreach ($a->requerimientos as $r) {
            if (! $r['obligatorio']) {
                continue;   // si no es obligatorio es una preferencia y no bloquea
            }

            $hecho = $a->hechos[$r['clave']] ?? null;

            if ($hecho === null) {
                $hallazgos[] = new Hallazgo(
                    'R02',
                    Severidad::Error,
                    "La arquitectura no aporta ningún dato para el requisito obligatorio «{$r['clave']}».",
                    ['requisito' => $r['clave']],
                );

                continue;
            }

            if (! $this->cumple($hecho, $r['operador'], $r['valor'], $r['tipo'])) {
                $hallazgos[] = new Hallazgo(
                    'R02',
                    Severidad::Error,
                    "Requisito obligatorio incumplido: {$r['clave']} {$r['operador']} {$r['valor']} "
                    . "(la arquitectura ofrece «{$this->texto($hecho)}»).",
                    ['requisito' => $r['clave'], 'esperado' => $r['valor'], 'obtenido' => $hecho],
                );
            }
        }

        return $hallazgos;
    }

    // compara lo que tiene la arquitectura con lo que pide el requisito
    public function cumple(mixed $hecho, string $operador, string $valor, string $tipo): bool
    {
        $tipoDato = TipoDato::from($tipo);

        if (! $tipoDato->admite($operador)) {
            throw new \InvalidArgumentException(
                "El operador «{$operador}» no es válido para el tipo «{$tipo}»."
            );
        }

        return match ($tipoDato) {
            TipoDato::Numero   => $this->comparaNumero((float) $hecho, $operador, (float) $valor),
            TipoDato::Booleano => $this->comoBooleano($hecho) === $this->comoBooleano($valor),
            TipoDato::Texto    => $this->comparaTexto($hecho, $operador, $valor),
            TipoDato::Lista    => $this->contenidoEn($hecho, $valor),
        };
    }

    private function comparaNumero(float $hecho, string $operador, float $valor): bool
    {
        return match ($operador) {
            '>=' => $hecho >= $valor,
            '<=' => $hecho <= $valor,
            '='  => abs($hecho - $valor) < 0.00001,
            '!=' => abs($hecho - $valor) >= 0.00001,
        };
    }

    private function comparaTexto(mixed $hecho, string $operador, string $valor): bool
    {
        if ($operador === 'in') {
            return $this->contenidoEn($hecho, $valor);
        }

        $iguales = $this->texto($hecho) === mb_strtolower(trim($valor));

        return $operador === '=' ? $iguales : ! $iguales;
    }

    // para el operador in: si el hecho es una lista vale con que coincida uno
    private function contenidoEn(mixed $hecho, string $valor): bool
    {
        $admitidos = array_map(
            fn ($v) => mb_strtolower(trim($v)),
            explode(',', $valor)
        );

        $valores = is_array($hecho) ? $hecho : [$hecho];

        foreach ($valores as $v) {
            if (in_array($this->texto($v), $admitidos, true)) {
                return true;
            }
        }

        return false;
    }

    private function comoBooleano(mixed $v): bool
    {
        return in_array($this->texto($v), ['1', 'true', 'si', 'sí'], true);
    }

    private function texto(mixed $v): string
    {
        if (is_bool($v)) {
            return $v ? '1' : '0';
        }

        if (is_array($v)) {
            return implode(', ', $v);
        }

        return mb_strtolower(trim((string) $v));
    }

    // ---------------------------------------------------------------- R03

    // R03: minimo 3 elementos y de al menos 2 tipos distintos
    private function r03ComposicionMinima(ArquitecturaEvaluable $a): array
    {
        $elementos = count($a->elementos);
        $tipos     = count($a->tipos());

        if ($elementos >= 3 && $tipos >= 2) {
            return [];
        }

        return [new Hallazgo(
            'R03',
            Severidad::Error,
            "Una arquitectura necesita al menos 3 elementos de 2 tipos distintos; "
            . "ésta tiene $elementos elemento(s) de $tipos tipo(s).",
            ['elementos' => $elementos, 'tipos' => $tipos],
        )];
    }

    // ---------------------------------------------------------------- R04

    // R04: lo que se paga al mes no puede pasar del presupuesto maximo del proyecto
    private function r04PresupuestoMaximo(ArquitecturaEvaluable $a): array
    {
        if ($a->totalMensualCentimos <= $a->presupuestoMaxCentimos) {
            return [];
        }

        $exceso = $a->totalMensualCentimos - $a->presupuestoMaxCentimos;

        return [new Hallazgo(
            'R04',
            Severidad::Error,
            'El coste mensual (' . CalculadoraPrecios::euros($a->totalMensualCentimos)
            . ') supera el presupuesto máximo del proyecto ('
            . CalculadoraPrecios::euros($a->presupuestoMaxCentimos) . ') en '
            . CalculadoraPrecios::euros($exceso) . '.',
            ['exceso_centimos' => $exceso],
        )];
    }

    // ---------------------------------------------------------------- R05

    // R05 (aviso): no hay ningun backup
    private function r05CopiaSeguridad(ArquitecturaEvaluable $a): array
    {
        if (in_array('backup', $a->tipos(), true)) {
            return [];
        }

        return [new Hallazgo(
            'R05',
            Severidad::Aviso,
            'La arquitectura no incluye ningún servicio de copia de seguridad. '
            . 'Se puede emitir el presupuesto, pero conviene revisarlo con el cliente.',
        )];
    }

    // ---------------------------------------------------------------- R06

    // R06 (aviso): todo esta en la misma region, si se cae esa region se cae todo
    private function r06RedundanciaGeografica(ArquitecturaEvaluable $a): array
    {
        $regiones = $a->regiones();

        if (count($regiones) > 1 || count($a->elementos) === 0) {
            return [];
        }

        return [new Hallazgo(
            'R06',
            Severidad::Aviso,
            'Todos los elementos están en la región ' . $regiones[0]
            . '. Una caída de esa región dejaría el servicio completo fuera de línea.',
            ['region' => $regiones[0]],
        )];
    }
}
