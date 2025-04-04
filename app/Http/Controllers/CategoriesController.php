<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    public function index (){
        return response()->json(['categorias'=>Categories::all()]);
    }
    public function store(Request $request)
    {
        $normalizedName = Str::ascii(strtolower($request->name));
    
       
        $exists = DB::table('categorias')
            ->whereRaw("LOWER(name) = ?", [$normalizedName])
            ->exists();
    
        if ($exists) {
            return response()->json([
                'message' => 'La Categoria ya existe',
                'error_code' => 409
            ], 409);
        }
      $categorias=new Categories;
      $categorias->name = $request->name;
      $categorias->range_course = json_encode($request->range_course);
      $categorias->area_id = $request->area_id;
      $categorias->save();
      return response()->json([
        'error_code' => 200,
        'message' => 'Categoria creada con éxito'
    ], 201);
    }
}
