<?php

namespace App\Validators;


class CompetitorValidator extends PersonalDataValidator
{

    public static function rules(string $prefix = ""): array
    {

        $dot = $prefix ? $prefix . '.' : '';

        return array_merge(
            parent::rules($prefix),
            SchoolDataValidator::rules("{$dot}school_data"),
            [
                "{$dot}school_data" => 'required|array',
                "{$dot}selected_areas" => 'required|array',
            ],
            SelectedAreaValidator::rules("{$dot}selected_areas.*"),
        );
    }

    public static function messages(string $prefix = ""): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return array_merge([
            parent::messages(),
            SchoolDataValidator::messages("{$dot}school_data"),
            SelectedAreaValidator::messages("{$dot}selected_areas"),
        ]);
    }
}
