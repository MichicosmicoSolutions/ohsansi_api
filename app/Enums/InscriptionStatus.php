<?php

namespace App\Enums;


class InscriptionStatus
{

    public const PENDING = 'pending';
    public const ACTIVE  = 'active';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';
    public const CONFIRMED = 'confirmed';


    public static function getValues(): array
    {
        return [
            self::PENDING,
            self::ACTIVE,
            self::REJECTED,
            self::CANCELLED,
            self::CONFIRMED,
        ];
    }
}
