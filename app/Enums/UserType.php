<?php

namespace App\Enums;

class UserType
{
    public const ADMIN = 'admin';
    public const USER = 'user';

    public static function getValues(): array
    {
        return [
            self::ADMIN,
            self::USER,
        ];
    }
}
