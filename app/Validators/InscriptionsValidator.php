<?php


namespace App\Validators;

use App\Validators\Contracts\ValidatesInput;

class InscriptionsValidator implements ValidatesInput
{
    public static function rules(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return array_merge(
            [
                "{$dot}olympic_id" => 'required|exists:olympics,id',
                "{$dot}accountable" => 'required|array',
                "{$dot}legal_tutor" => 'required|array',
                "{$dot}competitor" => 'required|array'
            ],
            PersonalDataValidator::rules("{$dot}accountable"),
            PersonalDataValidator::rules("{$dot}legal_tutor"),
            CompetitorValidator::rules("{$dot}competitor")
        );
    }

    public static function messages(string $prefix = ""): array
    {
        $dot = $prefix ? $prefix . "." : "";
        return array_merge(
            [
                "{$dot}accountable.required" => "El accountable es obligatorio.",
                "{$dot}legal_tutor.required " => "El tutor legal es obligatorio.",
                "{$dot}competitor.required" => "El competidor es obligatorio.",
                "{$dot}accountable.array" => "El accountable debe ser un array.",
                "{$dot}legal_tutor.array" => "El tutor legal debe ser un array.",
                "{$dot}competitor.array" => "El competidor debe ser un array.",

            ],
        );
    }
}
