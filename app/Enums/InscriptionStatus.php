<?php

namespace App\Enums;


class InscriptionStatus
{

    public const PENDING = 'pending';
    public const COMPLETED  = 'completed';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';


    public static function getValues(): array
    {
        return [
            self::PENDING,
            self::COMPLETED,
            self::REJECTED,
            self::CANCELLED,
        ];
    }
}
