<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\PersonalData;
use App\Models\Inscriptions;
use App\Models\LegalTutors;
use App\Models\BoletaDePago;
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

 
public function searchByStatus($status, Request $request)
{
    $validStatuses = ['pending', 'completed', 'cancelled'];

    if (!in_array($status, $validStatuses)) {
        return response()->json([
            'message' => 'Estado no válido.',
            'valid_statuses' => $validStatuses,
        ], 400);
    }

    $perPage = 10;
    $page = $request->query('page', 1);

    $paginator = Inscriptions::with([
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
    ->paginate($perPage, ['*'], 'page', $page);

    $transformedItems = collect($paginator->items())->map(function ($inscription) {
        return [
            'id' => $inscription->id,
            'status' => $inscription->status,
            'drive_url' => $inscription->drive_url,
            'competitor' => $inscription->competitor_data ? $inscription->competitor_data->names . ' ' . $inscription->competitor_data->last_names : null,
            'school' => $inscription->school ? $inscription->school->name : null,
            'accountable' => $inscription->accountable && $inscription->accountable->personalData
                ? $inscription->accountable->personalData->names . ' ' . $inscription->accountable->personalData->last_names
                : null,
            'legal_tutor' => $inscription->legalTutor && $inscription->legalTutor->personalData
                ? $inscription->legalTutor->personalData->names . ' ' . $inscription->legalTutor->personalData->last_names
                : null,
            'olympiad' => $inscription->olympiad ? $inscription->olympiad->title : null,
            'selected_areas' => $inscription->selected_areas->map(function ($selectedArea) {
                return [
                    'area' => $selectedArea->area ? $selectedArea->area->name : null,
                    'category' => $selectedArea->category ? $selectedArea->category->name : null,
                    'teacher' => $selectedArea->teacher && $selectedArea->teacher->personalData
                        ? $selectedArea->teacher->personalData->names . ' ' . $selectedArea->teacher->personalData->last_names
                        : null,
                    'paid_at' => $selectedArea->paid_at,
                ];
            }),
            'created_at' => $inscription->created_at,
            'updated_at' => $inscription->updated_at,
        ];
    })->toArray();

    $paginated = new LengthAwarePaginator(
        $transformedItems,
        $paginator->total(),
        $paginator->perPage(),
        $paginator->currentPage(),
        ['path' => $request->url(), 'query' => $request->query()]
    );

  
    return response()->json($paginated);
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


public function filter(Request $request)
{
     $query = Inscriptions::query();

    if ($request->has('department')) {
        $query->whereHas('school', fn($q) => $q->where('department', $request->query('department')));
    }

    if ($request->has('province')) {
        $query->whereHas('school', fn($q) => $q->where('province', $request->query('province')));
    }

    if ($request->has('area_id')) {
        $query->whereHas('selected_areas', fn($q) => $q->where('area_id', $request->query('area_id')));
    }

    if ($request->has('category_id')) {
        $query->whereHas('selected_areas', fn($q) =>
            $q->whereHas('category', fn($q2) => $q2->where('id', $request->query('category_id')))
        );
    }

    if ($request->has('olympiad_id')) {
        $query->where('olympiad_id', $request->query('olympiad_id'));
    }

   if ($request->has('gender')) {
    $query->whereHas('competitor_data', function($q) use ($request) {
        $q->where('gender', $request->query('gender'));
    });
}


    if ($request->has(['birthdate_from', 'birthdate_to'])) {
        $query->whereHas('competitor_data', fn($q) => $q->whereBetween('birthdate', [
            $request->query('birthdate_from'),
            $request->query('birthdate_to')
        ]));
    }

    // Parámetros para la paginación
    $perPage = 10;
    $page = $request->query('page', 1);

    // Obtener los datos paginados con las relaciones
    $paginator = $query->with([
        'competitor_data',
        'school',
        'accountable.personalData',
        'legalTutor.personalData',
        'selected_areas.area',
        'selected_areas.category',
        'selected_areas.teacher.personalData',
        'olympiad'
    ])->paginate($perPage, ['*'], 'page', $page);

    // Transformamos solo los items actuales paginados
 $transformedItems = collect($paginator->items())->map(function ($inscription) {
    return [
        'id' => $inscription->id,
        'status' => $inscription->status,
        'drive_url' => $inscription->drive_url,
        'competitor' => $inscription->competitor_data
            ? $inscription->competitor_data->names . ' ' . $inscription->competitor_data->last_names
            : null,
        'birthdate' => $inscription->competitor_data ? $inscription->competitor_data->birthdate : null,
        'gender' => $inscription->competitor_data ? $inscription->competitor_data->gender : null,
        'school' => $inscription->school ? $inscription->school->name : null,
        'school_department' => $inscription->school && $inscription->school->department
            ? $inscription->school->department
            : null,
        'school_province' => $inscription->school && $inscription->school->province
            ? $inscription->school->province
            : null,
        'accountable' => $inscription->accountable && $inscription->accountable->personalData
            ? $inscription->accountable->personalData->names . ' ' . $inscription->accountable->personalData->last_names
            : null,
        'legal_tutor' => $inscription->legalTutor && $inscription->legalTutor->personalData
            ? $inscription->legalTutor->personalData->names . ' ' . $inscription->legalTutor->personalData->last_names
            : null,
        'olympiad' => $inscription->olympiad ? $inscription->olympiad->title : null,
        'olympiad_price' => $inscription->olympiad ? $inscription->olympiad->price : null,
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


    // Crear un nuevo paginador con la colección transformada y datos de paginación originales
    $paginated = new LengthAwarePaginator(
        $transformedItems,
        $paginator->total(),
        $paginator->perPage(),
        $paginator->currentPage(),
        ['path' => $request->url(), 'query' => $request->query()]
    );

    return response()->json($paginated);
}
public function getBoletasByCiAndBirthdate(Request $request)
{
    $request->validate([
        'ci' => 'required|string',
        'birthdate' => 'required|date',
    ]);

    $person = PersonalData::where('ci', $request->ci)
        ->where('birthdate', $request->birthdate)
        ->first();

    if (!$person) {
        return response()->json(['message' => 'Persona no encontrada'], 404);
    }

    // Traemos inscripciones con boletas y olimpiadas relacionadas
    $inscriptions = Inscriptions::where('competitor_data_id', $person->id)
        ->whereNotNull('boleta_de_pago_id')
        ->with(['boletaDePago', 'olympiad'])
        ->get();

    // Armamos la estructura con boleta, estado y olimpiada
    $result = $inscriptions->map(function ($inscription) {
        return [
            'boleta' => $inscription->boletaDePago,
            'estado_inscripcion' => $inscription->status,
            'olimpiada' => [
                'id' => $inscription->olympiad->id,
                'titulo' => $inscription->olympiad->title ?? null,
                'fecha_inicio' => $inscription->olympiad->start_date ?? null,
                'fecha_fin' => $inscription->olympiad->end_date ?? null,
            ],
        ];
    });

    return response()->json([
        'persona' => $person->names . ' ' . $person->last_names,
        'inscripciones' => $result,
    ]);
}



}

