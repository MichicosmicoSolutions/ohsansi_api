<?php

namespace App\Http\Controllers;


use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Enums\RangeCourse;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller
{
    public function index()
    {
        return response()->json(['categorias' => Categories::all()]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'range_course' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $validValues = RangeCourse::getValues();
                    foreach ($value as $item) {
                        if (!in_array($item, $validValues)) {
                            $fail("El valor '$item' en $attribute no es válido.");
                        }
                    }
                }
            ],
            'area_id' => 'required|integer|min:1'
        ], [
            'name.required' => 'El campo name es obligatorio.',
            'range_course.required' => 'El campo range_course es obligatorio.',
            'area_id.required' => 'El campo area_id es obligatorio.',
            'range_course.array' => 'El campo range_course debe ser un array.',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $normalizedName = Str::ascii(strtolower($request->name));

        $exists = DB::table('categories')
            ->whereRaw("LOWER(name) = ?", [$normalizedName])
            ->exists();

        if ($exists) {
            return response()->json([
                'errors' => [
                    'name' => ['El nombre de la categoría ya existe']
                ],
            ], 409);
        }

        $categories = new Categories;
        $categories->name = $request->name;
        $categories->range_course = json_encode($request->range_course);
        $categories->area_id = $request->area_id;
        $categories->save();

        return response()->json([
            'message' => 'Categoria creada con éxito'
        ], 201);
    }
}
