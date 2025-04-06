<?php

namespace App\Http\Controllers;

use App\Imports\InscriptionsImport;
use App\Services\InscriptionService;
use App\Validators\InscriptionsValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    protected $inscriptionService;

    public function __construct(InscriptionService $inscriptionService)
    {
        $this->inscriptionService = $inscriptionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated_data  = $validator->validate();

        $base64File = $validated_data['excel'];
        $decodedFile = base64_decode($base64File);

        $tempFilePath = storage_path('../public/temp_excel.xlsx');

        File::put($tempFilePath, $decodedFile);
        $spreedsheet = new InscriptionsImport();
        Excel::import($spreedsheet, $tempFilePath);
        File::delete($tempFilePath);
        $inscriptionData = [
            "legal_tutor" => $request->input('legal_tutor'),
            "academic_tutor" => $request->input('academic_tutor'),
            "competitors" => $spreedsheet->getData(),
        ];

        $validator = InscriptionsValidator::getValidator($inscriptionData);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $errors = InscriptionsValidator::validateInscriptions($validatedData);

        if ($errors) {
            return response()->json([
                "errors" => $errors,
            ], 422);
        }

        return $this->inscriptionService->createInscription($validatedData);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * 
     * 
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('../public/templates/Plantilla-inscripciones.xlsx'); // Ruta del archivo
        $fileName = 'Plantilla-inscripciones.xlsx';

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'Archivo no encontrado.'], 404);
        }

        return response()->download($filePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ]);
    }
}
