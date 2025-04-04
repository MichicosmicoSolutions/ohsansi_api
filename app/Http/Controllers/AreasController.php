<?php

namespace App\Http\Controllers;

use App\Models\Areas;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;


class AreasController extends Controller
{
    public function index (){
        return response()->json(['areas'=>Areas::all()]);
    }
    public function store(Request $request)
    {
        
        $normalizedName = Str::ascii(strtolower($request->name));
    
            
        $exists = DB::table('areas')
            ->whereRaw("LOWER(name) = ?", [$normalizedName])
            ->exists();
    
        if ($exists) {
            return response()->json([
                'message' => 'El área ya existe',
                'error_code' => 409
            ], 409);
        }
    
        // Guardar el nuevo área
        $area = new Areas;
        $area->name = $request->name;
        $area->description = $request->description;
        $area->monto_precio = $request->monto_precio;
        $area->save();
    
        return response()->json([
            'error' => 200,
            'message' => 'Área creada con éxito'
        ], 201);
    }

}
