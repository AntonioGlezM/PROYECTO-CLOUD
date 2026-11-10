<?php

namespace App\Enums;

// Formas de cobrar un producto del catalogo
enum Modalidad: string
{
    case Mensual   = 'mensual';    // tarifa x cantidad, se paga todos los meses
    case Unico     = 'unico';      // tarifa x cantidad, se paga una vez
    case Consumo   = 'consumo';    // tarifa x lo que se gasta, con un tope al mes

    public function etiqueta(): string
    {
        return match ($this) {
            self::Mensual => 'Mensual por unidad',
            self::Unico   => 'Pago único',
            self::Consumo => 'Por consumo con tope mensual',
        };
    }

    // mensual y consumo se repiten cada mes, el pago unico no
    public function esRecurrente(): bool
    {
        return $this !== self::Unico;
    }
}
