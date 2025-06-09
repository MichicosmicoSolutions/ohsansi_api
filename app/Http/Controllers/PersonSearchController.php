<?php

namespace App\Http\Controllers;

use App\Models\Accountables;
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
        'accountable.personalData',
        'legalTutor.personalData',
        'selected_areas.area',
        'selected_areas.category',
        'selected_areas.teacher.personalData',
        'boletaDePago',
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
            'boleta_de_pago' => $inscription->boletaDePago ? [
                'id' => $inscription->boletaDePago->id,
                'numero_orden_de_pago' => $inscription->boletaDePago->numero_orden_de_pago,
                'ci' => $inscription->boletaDePago->ci,
                'status' => $inscription->boletaDePago->status,
                'nombre' => $inscription->boletaDePago->nombre,
                'apellido' => $inscription->boletaDePago->apellido,
                'fecha_nacimiento' => $inscription->boletaDePago->fecha_nacimiento,
                'cantidad' => $inscription->boletaDePago->cantidad,
                'concepto' => $inscription->boletaDePago->concepto,
                'precio_unitario' => $inscription->boletaDePago->precio_unitario,
                'importe' => $inscription->boletaDePago->importe,
                'total' => $inscription->boletaDePago->total,
            ] : null,
            'selected_areas' => $inscription->selected_areas->map(function ($selectedArea) {
                return [
                    'area'  => $selectedArea->area ? $selectedArea->area->name : null,
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
            $query->whereHas('school', function ($q) use ($request) {
                $q->where('department', $request->query('department'));
            });
        }

        if ($request->has('province')) {
            $query->whereHas('school', function ($q) use ($request) {
                $q->where('province', $request->query('province'));
            });
        }

        if ($request->has('area_id')) {
            $query->whereHas('selected_areas', function ($q) use ($request) {
                $q->where('area_id', $request->query('area_id'));
            });
        }

        if ($request->has('category_id')) {
            $query->whereHas(
                'selected_areas',
                function ($q) use ($request) {
                    $q->whereHas('category', function ($q2) use ($request) {
                        $q2->where('id', $request->query('category_id'));
                    });
                }
            );
        }

        if ($request->has('olympiad_id')) {
            $query->where('olympiad_id', $request->query('olympiad_id'));
        }

        if ($request->has('gender')) {
            $query->whereHas('competitor_data', function ($q) use ($request) {
                $q->where('gender', $request->query('gender'));
            });
        }


        if ($request->has(['birthdate_from', 'birthdate_to'])) {
            $query->whereHas('competitor_data', function ($q) use ($request) {
                $q->whereBetween('birthdate', [
                    $request->query('birthdate_from'),
                    $request->query('birthdate_to')
                ]);
            });
        }

        $perPage = 10;
        $page = $request->query('page', 1);

    
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

    $boletas = BoletaDePago::where('ci', $request->ci)
        ->where('fecha_nacimiento', $request->birthdate)
        ->get();

    if ($boletas->isEmpty()) {
        return response()->json(['message' => 'No se encontraron boletas para los datos proporcionados'], 404);
    }

    return response()->json([
        'boletas' => $boletas
    ]);
}
public function searchAccountable($ci)
{
    $personalData = PersonalData::where('ci', $ci)->first();

    if (!$personalData) {
        return response()->json(['message' => 'CI no encontrado'], 404);
    }

    $accountable = Accountables::with('personalData')->where('personal_data_id', $personalData->id)->first();

    if (!$accountable) {
        return response()->json(['message' => 'El CI no pertenece a un responsable (accountable)'], 404);
    }

    return response()->json([
        'accountable_id' => $accountable->id,
        'personal_data' => $accountable->personalData
    ]);
}

}
