<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator as BaseValidator;
use App\Enums\Department;
use App\Enums\RangeCourse;
use App\Validators\Contracts\ValidatesInput;
use Illuminate\Validation\Rule;

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
