<?php

namespace App\Http\Controllers;

use App\Models\Areas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AreasController extends Controller
{
    public function index()
    {
        return response()->json(['areas' => Areas::all()]);
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);
        $areas = new Areas;
        $areas->name = $validatedData['name'];
        $areas->description = $validatedData['description'];
        $areas->price = $validatedData['price'];
        $areas->save();
        return response()->json([
            'message' => 'Área creada con éxito'
        ], 201);
    }
}
