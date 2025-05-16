<?php

namespace App\Http\Controllers;


use App\Models\PersonalData;
use App\Models\Inscriptions;
use App\Models\LegalTutors;
use Illuminate\Http\Request;

class PersonSearchController extends Controller
{

  public function index()
{
    $inscriptions = Inscriptions::with([
        'competitor_data',
        'school',
        'accountable',
        'legalTutor',
        'selected_areas.area',
        'selected_areas.category',
        'selected_areas.teacher',
        'olympiad'
    ])->get()->map(function ($inscription) {
        return [
            'id' => $inscription->id,
            'status' => $inscription->status,
            'drive_url' => $inscription->drive_url,
            'competitor' => $inscription->competitor_data ? $inscription->competitor_data->names . ' ' . $inscription->competitor_data->last_names : null,
            'school' => $inscription->school ? $inscription->school->name : null,
            'accountable' => $inscription->accountable ? $inscription->accountable->personalData->names . ' ' . $inscription->accountable->personalData->last_names : null,
            'legal_tutor' => $inscription->legalTutor ? $inscription->legalTutor->personalData->names . ' ' . $inscription->legalTutor->personalData->last_names : null,
            'olympiad' => $inscription->olympiad ? $inscription->olympiad->title : null,
            'selected_areas' => $inscription->selected_areas->map(function ($selectedArea) {
                return [
                    'area'  => $selectedArea->area ? $selectedArea->area->name : null,
                    'category' => $selectedArea->category ? $selectedArea->category->name : null,
                    'teacher' => $selectedArea->teacher ? $selectedArea->teacher->personalData->names . ' ' . $selectedArea->teacher->personalData->last_names : null,
                    'paid_at' => $selectedArea->paid_at,
                ];
            }),
            'created_at' => $inscription->created_at,
            'updated_at' => $inscription->updated_at,
        ];
    });

    return response()->json($inscriptions);
}
    public function searchStudent($ci)
    {
        return response()->json(PersonalData::where('ci', $ci)->first());
    }

 public function searchByStatus($status)
{
    // Estados válidos para validar entrada
    $validStatuses = ['pending', 'completed', 'cancelled'];

    if (!in_array($status, $validStatuses)) {
        return response()->json([
            'message' => 'Estado no válido.',
            'valid_statuses' => $validStatuses,
        ], 400);
    }

    $inscriptions = Inscriptions::with([
        'competitor_data',
        'school',
        'accountable.personalData',
        'legalTutor.personalData',
        'selected_areas.area',
        'selected_areas.category',
        'selected_areas.teacher.personalData',
        'olympiad'
    ])
    ->where('status', $status)
    ->get()
    ->map(function ($inscription) {
        return [
            'id' => $inscription->id,
            'status' => $inscription->status,
            'drive_url' => $inscription->drive_url,
            'competitor' => $inscription->competitor_data ? $inscription->competitor_data->names . ' ' . $inscription->competitor_data->last_names : null,
            'school' => $inscription->school ? $inscription->school->name : null,
            'accountable' => $inscription->accountable ? $inscription->accountable->personalData->names . ' ' . $inscription->accountable->personalData->last_names : null,
            'legal_tutor' => $inscription->legalTutor ? $inscription->legalTutor->personalData->names . ' ' . $inscription->legalTutor->personalData->last_names : null,
            'olympiad' => $inscription->olympiad ? $inscription->olympiad->title : null,
            'selected_areas' => $inscription->selected_areas->map(function ($selectedArea) {
                return [
                    'area'  => $selectedArea->area ? $selectedArea->area->name : null,
                    'category' => $selectedArea->category ? $selectedArea->category->name : null,
                    'teacher' => $selectedArea->teacher ? $selectedArea->teacher->personalData->names . ' ' . $selectedArea->teacher->personalData->last_names : null,
                    'paid_at' => $selectedArea->paid_at,
                ];
            }),
            'created_at' => $inscription->created_at,
            'updated_at' => $inscription->updated_at,
        ];
    });

    return response()->json([
        'message' => 'Inscripciones encontradas.',
        'data' => $inscriptions,
    ], 200);
}

    public function searchByArea($area_id)
    {
        $inscriptions = Inscriptions::with(['competitor.personalData', 'area'])
            ->where('area_id', $area_id)
            ->get()
            ->map(function ($inscription) {
                return [
                    'competitor_name' => $inscription->competitor->personalData->names . ' ' . $inscription->competitor->personalData->last_names,
                    'status' => $inscription->status,
                    'area' => $inscription->area->name ?? null,
                    'created_at' => $inscription->created_at,
                ];
            });

        return response()->json($inscriptions);
    }

    public function searchByDate(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $inscriptions = Inscriptions::with(['competitor.personalData', 'area'])
            ->whereBetween('created_at', [$from, $to])
            ->get()
            ->map(function ($inscription) {
                return [
                    'competitor_name' => $inscription->competitor->personalData->names . ' ' . $inscription->competitor->personalData->last_names,
                    'status' => $inscription->status,
                    'area' => $inscription->area->name ?? null,
                    'created_at' => $inscription->created_at,
                ];
            });

        return response()->json($inscriptions);
    }
    public function searchLegalTutor($ci)
{
    $personalData = PersonalData::where('ci', $ci)->first();

    if (!$personalData) {
        return response()->json(['message' => 'CI no encontrado'], 404);
    }

    $legalTutor = LegalTutors::with('personalData')->where('personal_data_id', $personalData->id)->first();

    if (!$legalTutor) {
        return response()->json(['message' => 'El CI no pertenece a un tutor legal'], 404);
    }

    return response()->json([
        'tutor_id' => $legalTutor->id,
        'personal_data' => $legalTutor->personalData
    ]);
}

public function storePersonalData(Request $request)
{
    $validatedData = $request->validate([
        'ci' => 'required|integer|unique:personal_data,ci',
        'ci_expedition' => 'required|string',
        'names' => 'required|string',
        'last_names' => 'required|string',
        'birthdate' => 'required|date',
        'email' => 'required|email|unique:personal_data,email',
        'phone_number' => 'required|string',
    ]);

    $personalData = PersonalData::create($validatedData);

    return response()->json([
        'message' => 'PersonalData creado exitosamente',
        'data' => $personalData
    ], 201);
}
public function storeLegalTutor(Request $request)
{
    $validatedData = $request->validate([
        'personal_data_id' => 'required|exists:personal_data,id|unique:legal_tutors,personal_data_id',
    ]);

    $legalTutor = LegalTutors::create($validatedData);

    return response()->json([
        'message' => 'LegalTutor creado exitosamente',
        'data' => $legalTutor
    ], 201);
}
public function filterByDepartment($department)
{
    $inscriptions = Inscriptions::whereHas('school', function($q) use ($department) {
        $q->where('department', $department);
    })->get();

    return response()->json($inscriptions);
}

public function filterByProvince($province)
{
    $inscriptions = Inscriptions::whereHas('school', function($q) use ($province) {
        $q->where('province', $province);
    })->get();

    return response()->json($inscriptions);
}

public function filterByArea($area_id)
{
    $inscriptions = Inscriptions::whereHas('selected_areas.area', function($q) use ($area_id) {
        $q->where('id', $area_id);
    })->get();

    return response()->json($inscriptions);
}

public function filterByCategory($category_id)
{
    $inscriptions = Inscriptions::whereHas('selected_areas.category', function($q) use ($category_id) {
        $q->where('id', $category_id);
    })->get();

    return response()->json($inscriptions);
}

public function filterByOlympiad($olympiad_id)
{
    $inscriptions = Inscriptions::where('olympiad_id', $olympiad_id)->get();

    return response()->json($inscriptions);
}

public function filterByBirthdate($from, $to) {
    $inscriptions = Inscriptions::whereHas('competitor_data', function($query) use ($from, $to) {
        $query->whereBetween('birthdate', [$from, $to]);
    })->with(['competitor_data', 'school', 'area',  'olympiad'])
    ->get();

    return response()->json($inscriptions);
}


}