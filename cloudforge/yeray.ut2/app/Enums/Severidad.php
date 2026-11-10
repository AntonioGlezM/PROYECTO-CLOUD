<?php

namespace App\Enums;

// Error = no deja emitir el presupuesto. Aviso = solo avisa
enum Severidad: string
{
    case Error = 'error';
    case Aviso = 'aviso';

    public function bloquea(): bool
    {
        return $this === self::Error;
    }
}
