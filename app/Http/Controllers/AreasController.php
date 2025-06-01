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

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Za-z0-9 ]+$/'
            ],
            'description' => 'nullable|string',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras sin tildes, números y espacios (sin acentos ni caracteres especiales).',

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $exists = DB::table('areas')
            ->whereRaw("name = ?", [$request->name])
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => [
                    'name' => ['El área ya existe']
                ],
            ], 409);
        }


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
