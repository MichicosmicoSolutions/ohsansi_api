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

    private function normalizeTitle($title)
    {
        // Convertir a minúsculas
        $normalized = mb_strtolower($title, 'UTF-8');
    
        // Eliminar signos de puntuación y caracteres especiales
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', '', $normalized);
    
        // Opcional: eliminar tildes
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        $normalized = preg_replace('/[^a-zA-Z0-9]/', '', $normalized);
    
        return $normalized;
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
                    $normalizedInput = $this->normalizeTitle($value);

                    $exists = Olympics::all()->some(function ($olympic) use ($normalizedInput) {
                        $existingNormalized = $this->normalizeTitle($olympic->title);
                        return $existingNormalized === $normalizedInput;
                    });

                    if ($exists) {
                        $fail('Ya existe una olimpiada');
                    }
                }
            ],
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'Presentation' => 'nullable|string',
            'Requirements' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'Contacts' => 'nullable|string',
            'awards' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $data = $request->all();
        $data['status'] = 'false';

        
        $data['Presentation'] = $data['Presentation'] ?? 'No especificado';
        $data['Requirements'] = $data['Requirements'] ?? 'No especificado';
        $data['start_date'] = $data['start_date'] ?? null;
        $data['end_date'] = $data['end_date'] ?? null;
        $data['Contacts'] = $data['Contacts'] ?? 'No especificado';
        $data['awards'] = $data['awards'] ?? 'No especificado';

        $olympic = $this->service->create($data);

        return response()->json($olympic, 201);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'Presentation' => 'nullable|string',
            'Requirements' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'Contacts' => 'nullable|string',
            'awards' => 'nullable|string',
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

    public function publish(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'nullable|integer|min:0',
            'Presentation' => 'nullable|string',
            'Requirements' => 'nullable|string',
            'awards' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'Contacts' => 'nullable|string',
            'status' => 'required|boolean', // se espera booleano desde el frontend
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->toArray()
            ], 422);
        }
    
        $data = $request->only([
            'Presentation',
            'Requirements',
            'awards',
            'start_date',
            'end_date',
            'Contacts',
            'status',
        ]);
    
        
        $data['status'] = $request->boolean('status') ? 'true' : 'false';
    
        $olympic = $this->service->update($id, $data);
    
        if (!$olympic) {
            return response()->json(['message' => 'Olympic not found'], 404);
        }
    
        return response()->json([
            'message' => 'Olimpiada actualizada correctamente',
            'data' => $olympic
        ], 200);
    }
    
    public function getOlympicInfo($id)
    {
        $olympic = Olympics::find($id, [
            'title',
            'description',
            'price',
            'status',
            'Presentation',
            'Requirements',
            'start_date',
            'end_date',
            'awards',
            'Contacts'
        ]);

        if (!$olympic) {
            return response()->json(['message' => 'Olympic not found'], 404);
        }

        return response()->json([
            'title' => $olympic->title,
            'description' => $olympic->description,
            'price'=>$olympic->  price,
            'status'=>$olympic->status,
            'Presentation' => $olympic->Presentation,
            'Requirements' => $olympic->Requirements,
            'Start_date' => $olympic->start_date,
            'End_date' => $olympic->end_date,
            'Awards' => $olympic->awards,
            'Contacts' => $olympic->Contacts,
        ], 200);
    }

    public function destroy($id)
    {
        $category = Olympics::find($id);

        if (!$category) {
            return response()->json(['message' => 'Olimpiada no encontrada.'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Olimpiada eliminada con éxito.'], 200);
    }
}
