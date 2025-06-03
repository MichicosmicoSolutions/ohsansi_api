<?php

namespace App\Http\Controllers;

use App\Models\Areas;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class AreasController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/areas",
     *     summary="List all areas with categories",
     *     tags={"Areas"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of areas with their associated categories",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Area")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(['areas' => Areas::with('categories')->get()]);
    }

private function normalizeString($string)
{
    // Convertimos a minúsculas (funciona bien con UTF-8)
    $string = mb_strtolower($string, 'UTF-8');

    // Reemplazamos tildes manualmente
    $string = strtr($string, [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'Á' => 'a',
        'É' => 'e',
        'Í' => 'i',
        'Ó' => 'o',
        'Ú' => 'u',
        'ñ' => 'n',
        'Ñ' => 'n'
    ]);

    // Quitamos espacios dobles y bordes
    $string = preg_replace('/\s+/', ' ', trim($string));

    return $string;
}



  public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 ]+$/u' // Acepta tildes, ñ, números, espacios
        ],
        'description' => 'nullable|string',
    ], [
        'name.required' => 'El nombre es obligatorio.',
        'name.string' => 'El nombre debe ser una cadena de texto.',
        'name.max' => 'El nombre no puede exceder los 255 caracteres.',
        'name.regex' => 'El nombre solo puede contener letras (con o sin tildes), números y espacios.',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

   $normalizedInputName = $this->normalizeString($request->name);

$existingNames = Areas::all()->pluck('name')->map(function ($name) {
    return $this->normalizeString($name);
});

if ($existingNames->contains($normalizedInputName)) {
    return response()->json([
        'errors' => [
            'name' => ['El área ya existe.']
        ]
    ], 409);
}

    // Crear el área
    $area = new Areas;
    $area->name = $request->name;
    $area->description = $request->description;
    $area->save();

    return response()->json([
        'error' => 200,
        'message' => 'Área creada con éxito'
    ], 201);
}



   public function destroy($id)
    {
        $area = Areas::find($id);

        if (!$area) {
            return response()->json([
                'errors' => 'Área no encontrada'
            ], 404);
        }

        $area->delete();

        return response()->json([
            'message' => 'Área eliminada con éxito'
        ], 200);
    }




}
