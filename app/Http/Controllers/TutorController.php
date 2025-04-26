<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TutorController extends Controller
{
   
    public function index()
    {
        $tutors = Tutor::all();
        return response()->json($tutors, 200);
    }

  
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string|max:255',
            'LastName' => 'required|string|max:255',
            'FechaNacimiento' => 'required|date',
            'TipoTutor' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->toArray()
            ], 422);
        }

        $tutor = Tutor::create($request->only([
            'Name', 'LastName', 'FechaNacimiento', 'TipoTutor'
        ]));

        return response()->json([
            'message' => 'Tutor registrado correctamente.',
            'data' => $tutor
        ], 201);
    }
}
