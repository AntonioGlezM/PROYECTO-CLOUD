<?php

namespace App\Services;

/** Se lanza cuando una o varias reglas de severidad error impiden emitir. */
class PresupuestoBloqueado extends \RuntimeException
{
    /** @param Hallazgo[] $hallazgos */
    public function __construct(public readonly array $hallazgos)
    {
        parent::__construct('El presupuesto no se puede emitir: hay reglas incumplidas.');
    }

    /** @return string[] */
    public function mensajes(): array
    {
        return array_map(
            fn (Hallazgo $h) => "{$h->regla} · {$h->mensaje}",
            array_filter($this->hallazgos, fn (Hallazgo $h) => $h->bloquea()),
        );
    }
}
