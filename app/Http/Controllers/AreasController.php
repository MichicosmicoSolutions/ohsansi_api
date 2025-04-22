<?php

namespace App\Http\Controllers;

use App\Models\Areas;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class AreasController extends Controller
{
    public function index()
    {
        return response()->json(['areas' => Areas::all()]);
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
            'price' => 'required|integer|min:0'
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no puede exceder los 255 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras sin tildes, números y espacios (sin acentos ni caracteres especiales).',
            'price.required' => 'El precio es obligatorio.',
            'price.integer' => 'El precio debe ser un número.',
            'price.min' => 'El precio debe ser mayor o igual a 0.'
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

        // Guardar el nuevo área
        $area = new Areas;
        $area->name = $request->name;
        $area->description = $request->description;
        $area->price = $request->price;
        $area->save();

        return response()->json([
            'error' => 200,
            'message' => 'Área creada con éxito'
        ], 201);
    }

    public function updatePrice(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric|min:0|max:99999'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $area = Areas::find($id);
        if (!$area) {
            return response()->json([
                'errors' => ['Área no encontrada']
            ], 404);
        }
        $area->price = $request->price;
        $area->save();

        return response()->json(['message' => 'Monto actualizado', 'area' => $area]);
    }
}
