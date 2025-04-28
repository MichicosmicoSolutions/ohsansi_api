<?php

namespace App\Validators\Contracts;

interface ValidatesInput
{

    public static function rules(string $prefix = ""): array;
    public static function messages(string $prefix = ""): array;
}
