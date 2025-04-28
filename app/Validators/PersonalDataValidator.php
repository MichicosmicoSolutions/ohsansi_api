<?php

namespace App\Validators;

use App\Validators\Contracts\ValidatesInput;

class PersonalDataValidator implements ValidatesInput
{
    public static function rules(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return [
            "{$dot}ci" => !$dot ? "required|integer" : "required_if:{$dot},!,null|integer",
            "{$dot}ci_expedition" => !$dot ? "required|string" : "required_if:{$dot},!,null|string",
            "{$dot}names" => !$dot ? "required|string" : "required_if:{$dot},!,null|string",
            "{$dot}last_names" => !$dot ? "required|string" : "required_if:{$dot},!,null|string",
            "{$dot}birthdate" => !$dot ? "required|date" : "required_if:{$dot},!,null|date",
            "{$dot}email" => !$dot ? "required|email" : "required_if:{$dot},!,null|email",
            "{$dot}phone_number" => !$dot ? "required|string" : "required_if:{$dot},!,null|string",
        ];
    }

    public static function messages(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return [
            "{$dot}ci.required" => 'El campo CI es obligatorio.',
            "{$dot}ci.integer" => 'El campo CI debe ser un número entero.',
            "{$dot}ci_expedition.required" => 'El campoexpedición de CI es obligatorio.',
            "{$dot}ci_expedition.string" => 'El campo expedición de CI debe ser una cadena de texto.',
            "{$dot}names.required" => 'El campo nombres es obligatorio.',
            "{$dot}names.string" => 'El campo nombres debe ser una cadena de texto.',
            "{$dot}last_names.required" => 'El campo apellidos es obligatorio.',
            "{$dot}last_names.string" => 'El campo apellidos debe ser una cadena de texto.',
            "{$dot}birthdate.required" => 'El campo fecha de nacimiento es obligatorio.',
            "{$dot}birthdate.date" => 'El campo fecha de nacimiento debe ser una fecha válida.',
            "{$dot}email.required" => 'El campo correo electrónico es obligatorio.',
            "{$dot}email.string" => 'El campo correo electrónico debe ser una cadena de texto.',
            "{$dot}email.email" => 'El campo correo electrónico no tiene un formato válido.',
            "{$dot}phone_number.required" => 'El campo número de teléfono es obligatorio.',
            "{$dot}phone_number.string" => 'El campo número de teléfono debe ser una cadena de texto.',
        ];
    }
}
