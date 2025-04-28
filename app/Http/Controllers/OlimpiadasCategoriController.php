<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OlimpycAndCategoria;

class OlimpiadasCategoriController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'olympic_id' => 'required|exists:olympics,id',
            'area_id' => 'required|exists:areas,id',
            'category_id' => 'required|exists:cotegoreis,id',
        ]);

        $association = OlimpycAndCategoria::create([
            'olympic_id' => $request->olympic_id,
            'area_id' => $request->area_id,
            'category_id' => $request->category_id,
        ]);

        return response()->json([
            'message' => 'Área y categoría asociadas correctamente a la olimpiada.',
            'data' => $association
        ], 201);
    }
}
