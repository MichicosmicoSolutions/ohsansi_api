<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\OlympicsService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OlympicsController extends Controller
{
    protected $service;

    public function __construct(OlympicsService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                $normalizedTitle = Str::ascii(strtolower($value));

                $exists = DB::table('olympics')
                    ->get()
                    ->some(function ($olympic) use ($normalizedTitle) {
                        return Str::ascii(strtolower($olympic->title)) === $normalizedTitle;
                    });

                if ($exists) {
                    $fail('Ya existe una olimpiada con ese título.');
                }
            }
        ],
        'description' => 'required|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors()->toArray()
        ], 422);
    }

    $olympic = $this->service->create($request->all());
    return response()->json($olympic, 201);
}   

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $olympic = $this->service->update($id, $request->all());

        if (!$olympic) {
            return response()->json(['message' => 'Olympic not found'], 404);
        }

        return response()->json($olympic, 200);
    }
}
