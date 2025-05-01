<?php

namespace App\Enums;

class Publish
{
    public const Borrador = 'borrador';
    public const Inscripcion = 'inscripción';
    public const Cerrado = 'cerrado';

    public static function getValues(): array
    {
        return [
            self::Borrador,
            self::Inscripcion,
            self::Cerrado,
        ];
    }
}
