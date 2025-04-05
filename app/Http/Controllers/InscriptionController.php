<?php

namespace App\Http\Controllers;

use App\Enums\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Enums\RangeCourse;
use App\Services\InscriptionService;
use App\Validators\InscriptionsValidator;
use Illuminate\Support\Facades\Log;

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
        $maxBirthDate = now()->subYears(6)->format('Y-m-d');

        $validator = InscriptionsValidator::getValidator($request->all());

        Log::info('Data: ' . print_r($request->all(), true));

        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }

        $validatedData = $validator->validated();

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
