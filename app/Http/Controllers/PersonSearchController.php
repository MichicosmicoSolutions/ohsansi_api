<?php

namespace App\Http\Controllers;


use App\Models\PersonalData;

class PersonSearchController extends Controller
{
    public function searchStudent($ci)
    {
        return response()->json(PersonalData::where('ci', $ci)->first());
    }
}
