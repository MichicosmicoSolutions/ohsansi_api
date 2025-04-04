<?php

namespace App\Http\Controllers;

use App\Enums\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Enums\RangeCourse;
use App\Services\InscriptionService;

class InscriptionController extends Controller
{
    protected $inscriptionService;

    public function __construct(InscriptionService $inscriptionService)
    {
        $this->inscriptionService = $inscriptionService;
    }

    public function index()
    {
        $inscriptions = $this->inscriptionService->getInscriptions();
        return response()->json([
            "data" => $inscriptions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $maxBirthDate = now()->subYears(6)->format('Y-m-d');

        $validator = Validator::make($request->all(), [
            'competitors' => 'required|array',
            'competitors.*.ci' => 'required|integer',
            'competitors.*.ci_expedition' => 'required|string',
            'competitors.*.names' => 'required|string',
            'competitors.*.last_names' => 'required|string',
            'competitors.*.birthdate' => 'required|date_format:Y-m-d',
            'competitors.*.email' => 'required|email',
            'competitors.*.phone_number' => 'required|string',
            'competitors.*.school_data' => 'required|array',
            'competitors.*.school_data.name' => 'required|string',
            'competitors.*.school_data.department' => [
                'required',
                'string',
                Rule::in(Department::getValues()),
            ],
            'competitors.*.school_data.province' => 'required|string',
            'competitors.*.school_data.course' => [
                'required',
                'string',
                Rule::in(RangeCourse::getValues()),
            ],
            'competitors.*.selected_areas' => 'required|array|max:2',
            'competitors.*.selected_areas.*' => 'required|integer|min:1',
            'legal_tutor' => 'required|array',
            'legal_tutor.ci' => 'required|integer',
            'legal_tutor.ci_expedition' => 'required|string',
            'legal_tutor.names' => 'required|string',
            'legal_tutor.last_names' => 'required|string',
            'legal_tutor.birthdate' => 'required|date_format:Y-m-d',
            'legal_tutor.email' => 'required|email',
            'legal_tutor.phone_number' => 'required|string',
            'academic_tutor' => 'required|array',
            'academic_tutor.ci' => 'required|integer',
            'academic_tutor.ci_expedition' => 'required|string',
            'academic_tutor.names' => 'required|string',
            'academic_tutor.last_names' => 'required|string',
            'academic_tutor.birthdate' => 'required|date_format:Y-m-d',
            'academic_tutor.email' => 'required|email',
            'academic_tutor.phone_number' => 'required|string',
        ], [
            'competitors.required' => 'El campo competidores es requerido.',
            'competitors.array' => 'El campo competidores debe ser un array.',
            'competitors.*.ci.required' => 'El campo CI es requerido para cada competidor.',
            'competitors.*.ci.integer' => 'El campo CI debe ser un número entero para cada competidor.',
            'competitors.*.ci_expedition.required' => 'El campo expedición de CI es requerido para cada competidor.',
            'competitors.*.ci_expedition.string' => 'El campo expedición de CI debe ser una cadena de texto para cada competidor.',
            'competitors.*.names.required' => 'El campo nombres es requerido para cada competidor.',
            'competitors.*.names.string' => 'El campo nombres debe ser una cadena de texto para cada competidor.',
            'competitors.*.last_names.required' => 'El campo apellidos es requerido para cada competidor.',
            'competitors.*.last_names.string' => 'El campo apellidos debe ser una cadena de texto para cada competidor.',
            'competitors.*.birthdate.required' => 'El campo fecha de nacimiento es requerido para cada competidor.',
            'competitors.*.birthdate.date_format' => 'El campo fecha de nacimiento debe estar en el formato Y-m-d para cada competidor.',
            'competitors.*.email.required' => 'El campo email es requerido para cada competidor.',
            'competitors.*.email.email' => 'El campo email debe ser una dirección de correo electrónico válida para cada competidor.',
            'competitors.*.phone_number.required' => 'El campo número de teléfono es requerido para cada competidor.',
            'competitors.*.phone_number.string' => 'El campo número de teléfono debe ser una cadena de texto para cada competidor.',
            'competitors.*.school_data.required' => 'El campo datos escolares es requerido para cada competidor.',
            'competitors.*.school_data.array' => 'El campo datos escolares debe ser un array para cada competidor.',
            'competitors.*.school_data.name.required' => 'El campo nombre de la escuela es requerido para cada competidor.',
            'competitors.*.school_data.name.string' => 'El campo nombre de la escuela debe ser una cadena de texto para cada competidor.',
            'competitors.*.school_data.department.required' => 'El campo departamento es requerido para cada competidor.',
            'competitors.*.school_data.department.string' => 'El campo departamento debe ser una cadena de texto para cada competidor.',
            'competitors.*.school_data.department.in' => 'El departamento seleccionado no es válido. Debe ser uno de los siguientes: ' .
                implode(', ', Department::getValues()) . '.',
            'competitors.*.school_data.province.required' => 'El campo provincia es requerido para cada competidor.',
            'competitors.*.school_data.province.string' => 'El campo provincia debe ser una cadena de texto para cada competidor.',
            'competitors.*.school_data.course.required' => 'El curso es requerido.',
            'competitors.*.school_data.course.string' => 'El curso debe ser una cadena de texto.',
            'competitors.*.school_data.course.in' => 'El curso no es válido. Debe ser uno de los siguientes: ' .
                implode(', ', RangeCourse::getValues()) . '.',
            'competitors.*.selected_areas.required' => 'El campo áreas seleccionadas es requerido para cada competidor.',
            'competitors.*.selected_areas.array' => 'El campo áreas seleccionadas debe ser un array para cada competidor.',
            'competitors.*.selected_areas.max' => 'El campo áreas seleccionadas debe contener máximo 2 elementos.',
            'competitors.*.selected_areas.*.required' => 'El campo áreas seleccionadas debe contener valores para cada competidor.',
            'competitors.*.selected_areas.*.integer' => 'Cada área seleccionada debe ser un número entero para cada competidor.',
            'competitors.*.selected_areas.*.min' => 'Cada área seleccionada debe ser un número entero mayor o igual a 1 para cada competidor.',
            'legal_tutor.required' => 'El campo tutor legal es requerido.',
            'legal_tutor.array' => 'El campo tutor legal debe ser un array.',
            'legal_tutor.ci.required' => 'El campo CI del tutor legal es requerido.',
            'legal_tutor.ci.integer' => 'El campo CI del tutor legal debe ser un número entero.',
            'legal_tutor.ci_expedition.required' => 'El campo expedición de CI del tutor legal es requerido.',
            'legal_tutor.ci_expedition.string' => 'El campo expedición de CI del tutor legal debe ser una cadena de texto.',
            'legal_tutor.names.required' => 'El campo nombres del tutor legal es requerido.',
            'legal_tutor.names.string' => 'El campo nombres del tutor legal debe ser una cadena de texto.',
            'legal_tutor.last_names.required' => 'El campo apellidos del tutor legal es requerido.',
            'legal_tutor.last_names.string' => 'El campo apellidos del tutor legal debe ser una cadena de texto.',
            'legal_tutor.birthdate.required' => 'El campo fecha de nacimiento del tutor legal es requerido.',
            'legal_tutor.birthdate.date_format' => 'El campo fecha de nacimiento del tutor legal debe estar en el formato Y-m-d.',
            'legal_tutor.email.required' => 'El campo email del tutor legal es requerido.',
            'legal_tutor.email.email' => 'El campo email del tutor legal debe ser una dirección de correo electrónico válida.',
            'legal_tutor.phone_number.required' => 'El campo número de teléfono del tutor legal es requerido.',
            'legal_tutor.phone_number.string' => 'El campo número de teléfono del tutor legal debe ser una cadena de texto.',
            'academic_tutor.required' => 'El campo tutor académico es requerido.',
            'academic_tutor.array' => 'El campo tutor académico debe ser un array.',
            'academic_tutor.ci.required' => 'El campo CI del tutor académico es requerido.',
            'academic_tutor.ci.integer' => 'El campo CI del tutor académico debe ser un número entero.',
            'academic_tutor.ci_expedition.required' => 'El campo expedición de CI del tutor académico es requerido.',
            'academic_tutor.ci_expedition.string' => 'El campo expedición de CI del tutor académico debe ser una cadena de texto.',
            'academic_tutor.names.required' => 'El campo nombres del tutor académico es requerido.',
            'academic_tutor.names.string' => 'El campo nombres del tutor académico debe ser una cadena de texto.',
            'academic_tutor.last_names.required' => 'El campo apellidos del tutor académico es requerido.',
            'academic_tutor.last_names.string' => 'El campo apellidos del tutor académico debe ser una cadena de texto.',
            'academic_tutor.birthdate.required' => 'El campo fecha de nacimiento del tutor académico es requerido.',
            'academic_tutor.birthdate.date_format' => 'El campo fecha de nacimiento del tutor académico debe estar en el formato Y-m-d.',
            'academic_tutor.email.required' => 'El campo email del tutor académico es requerido.',
            'academic_tutor.email.email' => 'El campo email del tutor académico debe ser una dirección de correo electrónico válida.',
            'academic_tutor.phone_number.required' => 'El campo número de teléfono del tutor académico es requerido.',
            'academic_tutor.phone_number.string' => 'El campo número de teléfono del tutor académico debe ser una cadena de texto.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();

        return $this->inscriptionService->createInscription($validatedData);
    }

    public function show($id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
    }
}
