<?php

namespace App\Http\Controllers;
use App\Models\Areas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AreasController extends Controller
{
    public function index (){
        return response()->json(['areas'=>Areas::all()]);
    }

    public function store(Request $request)
    {
      $areas=new Areas;
      $areas->name = $request->name;
      $areas->description = $request->description;
      $areas->monto_precio = $request->monto_precio;
      $areas->save();
      return response()->json([
        'message' => 'Área creada con éxito'
    ], 201);
    }
    
public function updateMonto(Request $request, $id) {
        $area = Areas::find($id);
        if (!$area) {
            return response()->json(['error' => 'Área no encontrada'], 404);
        }

        $request->validate([
            'monto_precio' => 'required|numeric|min:0|max:999'
        ]);

        $area->monto_precio = $request->monto_precio;
        $area->save();

        return response()->json(['message' => 'Monto actualizado', 'area' => $area]);
    }
}
