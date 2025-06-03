<?php

namespace App\Http\Controllers;

use App\Enums\Publish;
use App\Enums\RangeCourse;
use App\Models\Areas;
use App\Models\OlympiadAreas;
use App\Models\Olympiads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\OlympiadsService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;



class OlympiadsController extends Controller
{
    protected $service;

    public function __construct(OlympiadsService $service)
    {
        $this->service = $service;
    }

    private function normalizeTitle($title)
    {
       
        $normalized = mb_strtolower($title, 'UTF-8');
        $normalized = preg_replace('/[^\p{L}\p{N}]+/u', '', $normalized);
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        $normalized = preg_replace('/[^a-zA-Z0-9]/', '', $normalized);

        return $normalized;
    }

  public function index()
{
    $olympiads = Olympiads::with('areas')->get();

    return response()->json(['data' => $olympiads]);
}
    /**
     * @OA\Get(
     *      path="/olympiads/{id}/areas",
     *      operationId="OlympiadsGetAreas",
     *      tags={"Olympiads"},
     *      summary="Get areas for a specific olympiad",
     *      description="Returns the list of areas with their categories based on the olympiad ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Olympiad ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="course",
     *          in="query",
     *          required=false,
     *          description="Filter by course range (e.g., 4to Secundaria, 5to Secundaria)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="data", type="array", items=@OA\Items(ref="#/components/schemas/Area"))
     *          )
     *      ),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="errors", type="object", description="Detailed error message for bad requests.",
     *                 @OA\Property(property="param_name", type="string", example="Algo salió mal") 
     *             )
     *          )
     *      )
     * )
     */
  public function showAreas(Request $request)
{
    $olympicId  = $request->route('id');
    $queryParams = $request->all();

    $validator = Validator::make($queryParams, [
        'course' => ['sometimes', 'string', Rule::in(RangeCourse::getValues())],
    ], [
        'course.sometimes' => 'The course is not valid.',
        'course.string' => 'The course must be a string.',
        'course.in' => 'The course is not valid value.',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $areas = Areas::with(['olympiadCategories' => function ($query) use ($queryParams) {
        if (isset($queryParams['course'])) {
            $query->where('range_course', 'like', '%' . $queryParams['course'] . '%');
        }
    }])
    ->whereHas('olympiads', function ($query) use ($olympicId) {
        $query->where('olympiads.id', $olympicId);
    })
    ->get();

    // Eliminar duplicados por ID de olympiadCategories
    $areas->each(function ($area) {
        $area->olympiadCategories = $area->olympiadCategories->unique('id')->values();
    });

    return response()->json(['data' => $areas]);
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

                    $exists = Olympiads::all()->some(function ($olympiad) use ($normalizedInput) {
                        $existingNormalized = $this->normalizeTitle($olympiad->title);
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
        $data['publish'] = Publish::Borrador;

        // Campos por defecto
        $data['Presentation'] = $data['Presentation'] ?? 'No especificado';
        $data['Requirements'] = $data['Requirements'] ?? 'No especificado';
        $data['start_date'] = $data['start_date'] ?? null;
        $data['end_date'] = $data['end_date'] ?? null;
        $data['Contacts'] = $data['Contacts'] ?? 'No especificado';
        $data['awards'] = $data['awards'] ?? 'No especificado';

        $olympiad = $this->service->create($data);

        return response()->json($olympiad, 201);
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

        $olympiad = $this->service->update($id, $request->all());

        if (!$olympiad) {
            return response()->json(['message' => 'Olympiad not found'], 404);
        }

        return response()->json($olympiad, 200);
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

        $olympiad = $this->service->update($id, ['price' => $request->price]);

        if (!$olympiad) {
            return response()->json(['message' => 'Olympiad not found'], 404);
        }

        return response()->json([
            'message' => 'Precio actualizado exitosamente',
            'data' => $olympiad
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
            'status' => 'required|boolean', 
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
        $hasAreas = OlympiadAreas::where('olympiad_id', $id)->exists();

        if (!$hasAreas) {
            return response()->json([
                'message' => 'No se puede publicar la olimpiada sin áreas asociadas.',
            ], 400);
        }

    
        if (isset($data['end_date']) && Carbon::parse($data['end_date'])->isPast()) {
            $data['publish'] = Publish::Cerrado; // Establecer "cerrado" si ya pasó la fecha
        } else {
  
            $data['publish'] = $data['publish'] ?? Publish::Inscripcion;
        }

   
        $data['status'] = $request->boolean('status') ? 'true' : 'false';

  
        $olympiad = $this->service->update($id, $data);

        if (!$olympiad) {
            return response()->json(['message' => 'Olympiad not found'], 404);
        }

        return response()->json([
            'message' => 'Olimpiada actualizada correctamente',
            'data' => $olympiad
        ], 200);
    }

    public function getOlympicInfo($id)
    {
        $olympiad = Olympiads::find($id, [
            'title',
            'description',
            'price',
            'status',
            'presentation',
            'requirements',
            'start_date',
            'end_date',
            'awards',
            'contacts'
        ]);

        if (!$olympiad) {
            return response()->json(['message' => 'Olympiad not found'], 404);
        }

        return response()->json([
            'title' => $olympiad->title,
            'description' => $olympiad->description,
            'price' => $olympiad->price,
            'status' => $olympiad->status,
            'presentation' => $olympiad->presentation,
            'requirements' => $olympiad->Requirements,
            'Start_date' => $olympiad->start_date,
            'End_date' => $olympiad->end_date,
            'Awards' => $olympiad->awards,
            'contacts' => $olympiad->Contacts,
        ], 200);
    }

    public function destroy($id)
    {
        $category = Olympiads::find($id);

        if (!$category) {
            return response()->json(['message' => 'Olimpiada no encontrada.'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Olimpiada eliminada con éxito.'], 200);
    }
}
