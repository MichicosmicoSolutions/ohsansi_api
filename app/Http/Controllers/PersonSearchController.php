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
            'competitor.personalData',
            'legalTutor.personalData',
            'responsable.personalData',
            'area'
        ])->get()->map(function ($inscription) {
            return [
                'competitor_name' => $inscription->competitor->personalData->names . ' ' . $inscription->competitor->personalData->last_names,
                'legal_tutor_name' => $inscription->legalTutor->personalData->names . ' ' . $inscription->legalTutor->personalData->last_names ?? null,
                'responsable_name' => $inscription->responsable->personalData->names . ' ' . $inscription->responsable->personalData->last_names ?? null,
                'status' => $inscription->status,
                'area' => $inscription->area->name ?? null,
                'created_at' => $inscription->created_at,
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
        $inscriptions = Inscriptions::with(['competitor.personalData', 'area'])
            ->where('status', $status)
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
}