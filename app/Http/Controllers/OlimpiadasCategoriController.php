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


    $exists = OlimpycAndCategorias::where('olympic_id', $request->olympic_id)
        ->where('area_id', $request->area_id)
        ->where('category_id', $request->category_id)
        ->exists();

    if ($exists) {
        return response()->json([
            'error' => 409,
            'message' => 'Ya existe esta combinación de olimpiada, área y categoría.'
        ], 409);
    }

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
    public function getCategoriesByOlympicAndArea($olympic_id, $area_id)
    {
        $categories = OlimpycAndCategorias::where('olympic_id', $olympic_id)
            ->where('area_id', $area_id)
            ->with('category') // traer relación category
            ->get()
            ->pluck('category') // solo categorías
            ->filter() // elimina nulos
            ->values(); // reindexar

        return response()->json([
        
            'categories' => $categories
        ]);
    }

    
    public function getAreasByOlympic($olympic_id)
    {
        $areas = OlimpycAndCategorias::where('olympic_id', $olympic_id)
            ->with('area') // traer relación area
            ->get()
            ->pluck('area') // solo áreas
            ->unique('id') // evita duplicados
            ->filter()
            ->values();

        return response()->json([
          
            'areas' => $areas
        ]);
    }


    public function getAreasWithCategoriesByOlympic($olympic_id)
    {
        $data = OlimpycAndCategorias::where('olympic_id', $olympic_id)
            ->with(['area', 'category']) // traer area y category
            ->get()
            ->groupBy('area.id') // agrupar por área
            ->map(function ($group) {
                $area = $group->first()->area;
                $categories = $group->pluck('category')->filter()->values();
                return [
                    'area' => $area,
                    'categories' => $categories
                ];
            })
            ->values();

        return response()->json([
        
            'areas' => $data
        ]);
    }   
}
