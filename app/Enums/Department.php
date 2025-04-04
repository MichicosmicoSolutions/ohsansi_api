<?php

namespace App\Enums;

class Department
{
    public const CBBA = 'Cochabamba';
    public const LA_Paz = 'La Paz';
    public const ORURO = 'Oruro';
    public const POTOSI = 'Potosi';
    public const TARIJA = 'Tarija';
    public const SANTA_CRUZ = 'Santa Cruz';
    public const BENI = 'Beni';
    public const PANDO = 'Pando';

    public static function getValues(): array
    {
        return [
            self::CBBA,
            self::LA_Paz,
            self::ORURO,
            self::POTOSI,
            self::TARIJA,
            self::SANTA_CRUZ,
            self::BENI,
            self::PANDO,
        ];
    }
}
