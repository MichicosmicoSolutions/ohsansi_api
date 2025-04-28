<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OlimpycAndCategorias; 
class OlimpiadasCategoriController extends Controller
{
    public function store(Request $request)
    {
        
        $request->validate([
            'olympic_id' => 'required|exists:olympics,id',
            'area_id' => 'required|exists:areas,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $olimpiada = new OlimpycAndCategorias;
        $olimpiada->olympic_id = $request->olympic_id;
        $olimpiada->area_id = $request->area_id;
        $olimpiada->category_id = $request->category_id;
        $olimpiada->save();
        return response()->json([
            'error' => 200,
            'message' => 'Olimpiada creada exitosamente'
        ], 201);
    }
}
