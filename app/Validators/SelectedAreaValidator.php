<?php

namespace App\Validators;

use App\Validators\Contracts\ValidatesInput;

class SelectedAreaValidator implements ValidatesInput
{
    public static function rules(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return array_merge(
            [
                "{$dot}area_id" => 'required|integer',
                "{$dot}academic_tutor" => 'sometimes|array',

            ],
            PersonalDataValidator::rules("{$dot}academic_tutor")
        );
    }

    public static function messages(string $prefix = ""): array
    {
        $dot = $prefix ? $prefix . "." : "";
        return [
            "{$dot}area_id.required" => 'El campo área es obligatorio.',
            "{$dot}area_id.integer" => 'El campo área debe ser un número entero.',
            "{$dot}academic_tutor.required" => 'El campo tutor académico es obligatorio.',
            "{$dot}academic_tutor.array" => 'El campo tutor académico debe ser un array.'
        ] + PersonalDataValidator::messages("{$dot}academic_tutor");
    }
}
