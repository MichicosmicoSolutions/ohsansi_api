<?php

namespace App\Enums;


class RangeCourse
{
    public const C1P = "1ro Primaria";
    public const C2P = '2do Primaria';
    public const C3P = '3ro Primaria';
    public const C4P = '4to Primaria';
    public const C5P = '5to Primaria';
    public const C6P = '6to Primaria';
    public const C1S = '1ro Secundaria';
    public const C2S = '2do Secundaria';
    public const C3S = '3ro Secundaria';
    public const C4S = '4to Secundaria';
    public const C5S = '5to Secundaria';
    public const C6S = '6to Secundaria';

    public static function getValues(): array
    {
        return [
            self::C1P,
            self::C2P,
            self::C3P,
            self::C4P,
            self::C5P,
            self::C6P,
            self::C1S,
            self::C2S,
            self::C3S,
            self::C4S,
            self::C5S,
            self::C6S,
        ];
    }
}
