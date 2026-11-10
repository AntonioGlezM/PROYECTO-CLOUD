<?php

namespace App\Enums;

// Tipo de valor que tiene un requisito. Segun el tipo se pueden usar unos operadores u otros
enum TipoDato: string
{
    case Numero   = 'numero';
    case Texto    = 'texto';
    case Booleano = 'booleano';
    case Lista    = 'lista';

    // operadores que se pueden usar con cada tipo
    public function operadores(): array
    {
        return match ($this) {
            self::Numero   => ['>=', '<=', '=', '!='],
            self::Texto    => ['=', '!=', 'in'],
            self::Booleano => ['='],
            self::Lista    => ['in'],
        };
    }

    public function admite(string $operador): bool
    {
        return in_array($operador, $this->operadores(), true);
    }
}
