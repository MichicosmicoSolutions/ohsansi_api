<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Enums\RangeCourse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Normalizer;

class CategoriesController extends Controller



{
    public function index(Request $request)
    {
        $query = Categories::query();

        if ($request->has('area_id')) {
            $query->where('area_id', $request->input('area_id'));
        }
        return response()->json(['categorias' => $query->get()]);
    }

    public function getCategoriasPorArea($area_id)
    {
        $categorias = Categories::with('area')
            ->where('area_id', $area_id)
            ->get();

        return response()->json(['categorias' => $categorias]);
    }

 private function normalizeName(string $name): string
{
  
    $name = mb_strtolower($name, 'UTF-8');

 
    if (class_exists('Normalizer')) {
        $name = \Normalizer::normalize($name, \Normalizer::FORM_D);
    }

    
    $name = preg_replace('/\p{Mn}/u', '', $name);

   
    $name = preg_replace('/[^a-z0-9 ]/u', '', $name);

   
    $name = preg_replace('/\s+/', ' ', $name);
    $name = trim($name);

    return $name;
}



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
    $normalizedInput = $this->normalizeName($value);

    $exists = DB::table('categories')
        ->where('area_id', $request->area_id)
        ->get()
        ->some(function ($cat) use ($normalizedInput) {
            $normalizedExisting = $this->normalizeName($cat->name);
            return $normalizedExisting === $normalizedInput;
        });

    if ($exists) {
        $fail('La categoría ya existe en esta área.');
    }
}

            ],
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

        $categories = new Categories;
        $categories->name = $request->name;
        $categories->range_course = $request->range_course;
        $categories->area_id = $request->area_id;
        $categories->save();

        return response()->json([
            'message' => 'Categoría creada con éxito'
        ], 201);
    }
    public function destroy($id)
    {
        $category = Categories::find($id);

        if (!$category) {
            return response()->json(['message' => 'Categoría no encontrada.'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Categoría eliminada con éxito.'], 200);
    }
}
