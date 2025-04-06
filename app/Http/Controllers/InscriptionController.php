<?php

namespace App\Http\Controllers;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\InscriptionService;
use App\Validators\InscriptionsValidator;

class InscriptionController extends Controller
{
    protected $inscriptionService;

    public function __construct(InscriptionService $inscriptionService)
    {
        $this->inscriptionService = $inscriptionService;
    }

    public function index()
    {
        $inscriptions = $this->inscriptionService->getInscriptions();
        return response()->json([
            "data" => $inscriptions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {

        $validator = InscriptionsValidator::getValidator($request->all());

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
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

    public function show($id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
    }
}
