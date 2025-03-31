<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller
{
    public function index()
    {
        return response()->json(['Categories' => Categories::all()]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'range_course' => 'required|array',
            'area_id' => 'required|integer|exists:areas,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = new Categories;
        $category->name = $request->name;
        $category->range_course = json_encode($request->range_course);
        $category->area_id = $request->area_id;
        $category->save();
        return response()->json([
            'message' => 'Área creada con éxito'
        ], 201);
    }
}
