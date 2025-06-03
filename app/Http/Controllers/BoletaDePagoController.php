<?php

namespace App\Http\Controllers;

use App\Models\BoletaDePago;
use Illuminate\Http\Request;

class BoletaDePagoController extends Controller
{

    public function index()
    {
        $boletas = BoletaDePago::all();
        return response()->json($boletas, 200);
    }

  
    public function store(Request $request)
    {
        $boleta = BoletaDePago::create([
            'numero_orden_de_pago' => $request->input('numero_orden_de_pago'),
            'ci'=> $request->input('ci'),
            'status' => 'pending',
            'nombre' => $request->input('nombre'),
            'apellido' => $request->input('apellido'),
            'fecha_nacimiento' => $request->input('fecha_nacimiento'),
            'cantidad' => $request->input('cantidad'),
            'concepto' => $request->input('concepto'),
            'precio_unitario' => $request->input('precio_unitario'),
            'importe' => $request->input('importe'),
            'total' => $request->input('total'),
             
        ]);

        return response()->json([
            'message' => 'Boleta de pago creada correctamente',
            'boleta' => $boleta
        ], 201);
    }
}
