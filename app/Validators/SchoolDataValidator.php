<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator as BaseValidator;
use App\Enums\Department;
use App\Enums\RangeCourse;
use App\Validators\Contracts\ValidatesInput;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="SchoolData",
 *     title="School Data",
 *     description="Represents the school data for a competitor.",
 *     required={"name", "department", "province", "course"},
 *     @OA\Property(property="name", type="string", description="The name of the school."),
 *     @OA\Property(property="department", type="string", description="The department where the school is located.", enum={
 *         "Cochabamba",
 *         "La Paz",
 *         "Oruro",
 *         "Potosi",
 *         "Tarija",
 *         "Santa Cruz",
 *         "Beni",
 *         "Pando"
 *     }),
 *     @OA\Property(property="province", type="string", description="The province where the school is located."),
 *     @OA\Property(property="course", type="string", description="The course the competitor is enrolled in.", enum={
 *         "1ro Primaria",
 *         "2do Primaria",
 *         "3ro Primaria",
 *         "4to Primaria",
 *         "5to Primaria",
 *         "6to Primaria",
 *         "1ro Secundaria",
 *         "2do Secundaria",
 *         "3ro Secundaria",
 *         "4to Secundaria",
 *         "5to Secundaria",
 *         "6to Secundaria"
 *     })
 * )
 */
class SchoolDataValidator implements ValidatesInput
{
    public static function rules(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return [
            "{$dot}name" => 'required|string',
            "{$dot}department" => ['required', 'string', Rule::in(Department::getValues())],
            "{$dot}province" => 'required|string',
            "{$dot}course" => ['required', 'string', Rule::in(RangeCourse::getValues())],
        ];
    }

    public static function messages(string $prefix = ''): array
    {
        $dot = $prefix ? $prefix . '.' : '';
        return [
            "{$dot}name.required" => 'El campo nombre es obligatorio.',
            "{$dot}name.string" => 'El campo nombre debe ser una cadena de texto.',
            "{$dot}department.required" => 'El campo departamento es obligatorio.',
            "{$dot}department.string" => 'El campo departamento debe ser una cadena de texto.',
            "{$dot}department.in" => 'El valor del campo departamento no es válido.',
            "{$dot}province.required" => 'El campo provincia es obligatorio.',
            "{$dot}province.string" => 'El campo provincia debe ser una cadena de texto.',
            "{$dot}course.required" => 'El campo curso es obligatorio.',
            "{$dot}course.string" => 'El campo curso debe ser una cadena de texto.',
            "{$dot}course.in" => 'El valor del campo curso no es válido.',
            "{$dot}year_of_graduation.required" => 'El campo año de graduación es obligatorio.',
            "{$dot}year_of_graduation.integer" => 'El campo año de graduación debe ser un número entero.',
            "{$dot}average_grade.required" => 'El campo promedio es obligatorio.',
            "{$dot}average_grade.numeric" => 'El campo promedio debe ser un valor numérico.',
            "{$dot}average_grade.between" => 'El campo promedio debe estar entre 0 y 10.',
        ];
    }
}
