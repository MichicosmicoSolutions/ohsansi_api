<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicTutors;
use App\Models\PersonalData;
use App\Models\Inscriptions;

class PersonSearchController extends Controller
{

    public function index()
    {
        return response()->json(['Inscriptions' => Inscriptions::all()]);
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
}
