<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\OlympicsService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Olympics;
class OlympicsController extends Controller
{
    protected $service;

    public function __construct(OlympicsService $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        return response()->json(['Olympics' => Olympics::all()]);
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
            'price' => 'required|integer|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        // Agregar el status por defecto
        $data = $request->all();
        $data['status'] = 'No Publico';

        $olympic = $this->service->create($data);

        return response()->json($olympic, 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
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

    public function updatePrice(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'price' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $olympic = $this->service->update($id, ['price' => $request->price]);

        if (!$olympic) {
            return response()->json(['message' => 'Olympic not found'], 404);
        }

        return response()->json([
            'message' => 'Precio actualizado exitosamente',
            'data' => $olympic
        ], 200);
    }

    // Nuevo método para publicar una olimpiada
    public function publish($id)
    {
        $olympic = $this->service->update($id, [
            'status' => 'Publico',
            'end_date' => Carbon::now(), // Fecha actual
        ]);

        if (!$olympic) {
            return response()->json(['message' => 'Olympic not found'], 404);
        }

        return response()->json([
            'message' => 'Olimpiada publicada exitosamente',
            'data' => $olympic
        ], 200);
    }
}
