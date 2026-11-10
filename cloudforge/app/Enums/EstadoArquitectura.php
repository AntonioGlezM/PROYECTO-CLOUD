<?php

namespace App\Enums;

enum EstadoArquitectura: string
{
    case Borrador  = 'borrador';
    case Validada  = 'validada';
    case Rechazada = 'rechazada';
}
