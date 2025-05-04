<?php

namespace App\Validators;

use App\Validators\Contracts\ValidatesInput;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="PersonalDataRequest",
 *     required={"ci", "ci_expedition", "names", "last_names"},
 *     @OA\Property(property="ci", type="integer", example="12345678"),
 *     @OA\Property(property="ci_expedition", type="string", example="Cochabamba"),
 *     @OA\Property(property="names", type="string", example="Juan"),
 *     @OA\Property(property="last_names", type="string", example="Perez"),
 *     @OA\Property(property="birthdate", type="string", format="date", example="2015-01-01"),
 *     @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com"),
 *     @OA\Property(property="phone_number", type="string", example="+591789456123"),
 *     @OA\Property(property="gender", type="string", example="M"),
 * )
 */
class PersonalDataValidator implements ValidatesInput
{
    public static function rules(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return [
            "{$dot}ci" => !$dot ? "required|integer" : "required_with:{$prefix}|integer",
            "{$dot}ci_expedition" => !$dot ? "required|string" : "required_with:{$prefix}|string",
            "{$dot}names" => !$dot ? "required|string" : "required_with:{$prefix}|string",
            "{$dot}last_names" => !$dot ? "required|string" : "required_with:{$prefix}|string",
            "{$dot}birthdate" => !$dot ? "required|date" : "required_with:{$prefix}|date",
            "{$dot}email" => !$dot ? "required|email" : "required_with:{$prefix}|email",
            "{$dot}phone_number" => !$dot ? "required|string" : "required_with:{$prefix}|string",
            "{$dot}gender" => !$dot ? [
                "required",
                "string",
                Rule::in(['F', 'M'])
            ] : [
                "required_with:{$prefix}",
                "string",
                Rule::in(['F', 'M'])
            ]
        ];
    }

    public static function messages(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return [
            "{$dot}ci.required" => 'El campo CI es obligatorio.',
            "{$dot}ci.required_with" => "El campo CI es obligatorio para {$prefix}",
            "{$dot}ci.integer" => 'El campo CI debe ser un número entero.',
            "{$dot}ci_expedition.required" => 'El campoexpedición de CI es obligatorio.',
            "{$dot}ci_expedition.required_with" => "El campo expedición de CI es obligatorio para {$prefix}",
            "{$dot}ci_expedition.string" => 'El campo expedición de CI debe ser una cadena de texto.',
            "{$dot}names.required" => 'El campo nombres es obligatorio.',
            "{$dot}names.required_with" => "El campo nombres es obligatorio para {$prefix}",
            "{$dot}names.string" => 'El campo nombres debe ser una cadena de texto.',
            "{$dot}last_names.required" => 'El campo apellidos es obligatorio.',
            "{$dot}last_names.required_with" => "El campo apellidos es obligatorio para {$prefix}",
            "{$dot}last_names.string" => 'El campo apellidos deben ser una cadena de texto.',
            "{$dot}birthdate.required" => 'El campo fecha de nacimiento es obligatorio.',
            "{$dot}birthdate.required_with" => "El campo fecha de nacimiento es obligatorio para {$prefix}",
            "{$dot}birthdate.date" => 'El campo fecha de nacimiento debe ser una fecha válida.',
            "{$dot}email.required" => 'El campo correo electrónico es obligatorio.',
            "{$dot}email.required_with" => "El campo correo electrónico es obligatorio para {$prefix}",
            "{$dot}email.string" => 'El campo correo electrónico debe ser una cadena de texto.',
            "{$dot}email.email" => 'El campo correo electrónico no tiene un formato válido.',
            "{$dot}phone_number.required" => 'El campo número de teléfono es obligatorio.',
            "{$dot}phone_number.required_with" => "El campo número de teléfono es obligatorio para {$prefix}",
            "{$dot}phone_number.string" => 'El campo número de teléfono debe ser una cadena de texto.',
            "{$dot}gender.required" => 'El campo género es obligatorio. (F o M)',
            "{$dot}gender.required_with" => "El campo género es obligatorio para {$prefix}. (F o M).",
            "{$dot}gender.in" => 'El campo género debe ser uno de los valores permitidos. (F o M)',
            "{$dot}gender.string" => 'El campo género debe ser una cadena de texto. (F o M)',
        ];
    }
}
