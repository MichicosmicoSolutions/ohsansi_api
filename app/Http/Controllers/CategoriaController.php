<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index (){
        return response()->json(['categorias'=>Categoria::all()]);
    }
    public function store(Request $request)
    {
      $categorias=new Categoria;
      $categorias->name = $request->name;
      $categorias->range_course = json_encode($request->range_course);
      $categorias->area_id = $request->area_id;
      $categorias->save();
      return response()->json([
        'message' => 'Área creada con éxito'
    ], 201);
    }
}
