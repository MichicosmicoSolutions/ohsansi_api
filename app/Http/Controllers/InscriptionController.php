<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\InscriptionService;
use App\Services\ResponsableService;
use Illuminate\Support\Facades\Validator;
use App\Validators\InscriptionsValidator;
use Illuminate\Support\Facades\Log;

/**
 * Class InscriptionController
 *
 * @package App\Http\Controllers
 * @author [Your Name]
 * @version 1.0
 */
class InscriptionController extends Controller
{
    /**
     * @var InscriptionService
     */
    protected $inscriptionService;

    /**
     * InscriptionController constructor.
     * @param InscriptionService $inscriptionService
     */
    public function __construct(InscriptionService $inscriptionService)
    {
        $this->inscriptionService = $inscriptionService;
    }


    /**
     * Get inscriptions for a user.
     *
     * @OA\Get(
     *     path="/api/inscriptions",
     *     summary="Get inscriptions for the authenticated user",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="competitor_id", type="integer"),
     *                     @OA\Property(property="drive_url", type="string", nullable=true),
     *                     @OA\Property(property="olympic_id", type="integer"),
     *                     @OA\Property(property="area_id", type="integer"),
     *                     @OA\Property(property="category_id", type="integer"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="competitor",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="school_id", type="integer"),
     *                         @OA\Property(property="legal_tutor_id", type="integer"),
     *                         @OA\Property(property="responsable_id", type="integer"),
     *                         @OA\Property(property="personal_data_id", type="integer"),
     *                         @OA\Property(property="course", type="string"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time"),
     *                         @OA\Property(
     *                             property="school",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="department", type="string"),
     *                             @OA\Property(property="province", type="string"),
     *                             @OA\Property(property="created_at", type="string", format="date-time"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="responsable",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="personal_data_id", type="integer"),
     *                         @OA\Property(property="code", type="string"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time"),
     *                         @OA\Property(
     *                             property="personal_data",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="ci", type="integer"),
     *                             @OA\Property(property="ci_expedition", type="string"),
     *                             @OA\Property(property="names", type="string"),
     *                             @OA\Property(property="last_names", type="string"),
     *                             @OA\Property(property="birthdate", type="string", format="date"),
     *                             @OA\Property(property="email", type="string"),
     *                             @OA\Property(property="phone_number", type="string")
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="legal_tutor",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="personal_data_id", type="integer"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time"),
     *                         @OA\Property(
     *                             property="personal_data",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="ci", type="integer"),
     *                             @OA\Property(property="ci_expedition", type="string"),
     *                             @OA\Property(property="names", type="string"),
     *                             @OA\Property(property="last_names", type="string"),
     *                             @OA\Property(property="birthdate", type="string", format="date"),
     *                             @OA\Property(property="email", type="string"),
     *                             @OA\Property(property="phone_number", type="string")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $token = $request->header('Authorization');
        if (!$token) {
            return response()->json([
                "error" => "Resource not found",
            ], 404);
        }

        $result = ResponsableService::decodeJWT($token);

        if ($result && isset($result['errors']) && !empty($result['errors'])) {
            return response()->json([
                'errors' => $result['errors'],
            ], 401);
        }

        $payload = $result['payload'];

        if (!$payload) {
            return response()->json([
                "error" => "Resource not found",
            ], 404);
        }

        $inscriptions = $this->inscriptionService->getInscriptions($payload['sub'], $payload['code']);

        return response()->json([
            "data" => $inscriptions,
        ]);
    }

    /**
     * Get a specific inscription by ID.
     *
     * @OA\Get(
     *     path="/api/inscriptions/{id}",
     *     summary="Get an inscription by ID",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Inscription ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="competitor_id", type="integer"),
     *                 @OA\Property(property="drive_url", type="string", nullable=true),
     *                 @OA\Property(property="olympic_id", type="integer"),
     *                 @OA\Property(property="area_id", type="integer"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *                 @OA\Property(
     *                     property="competitor",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="school_id", type="integer"),
     *                     @OA\Property(property="legal_tutor_id", type="integer"),
     *                     @OA\Property(property="responsable_id", type="integer"),
     *                     @OA\Property(property="personal_data_id", type="integer"),
     *                     @OA\Property(property="course", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="school",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="department", type="string"),
     *                         @OA\Property(property="province", type="string"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="responsable",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="personal_data_id", type="integer"),
     *                     @OA\Property(property="code", type="string"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="personal_data",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="ci", type="integer"),
     *                         @OA\Property(property="ci_expedition", type="string"),
     *                         @OA\Property(property="names", type="string"),
     *                         @OA\Property(property="last_names", type="string"),
     *                         @OA\Property(property="birthdate", type="string", format="date"),
     *                         @OA\Property(property="email", type="string"),
     *                         @OA\Property(property="phone_number", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="olympic",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="price", type="number"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="start_date", type="string", format="date"),
     *                     @OA\Property(property="end_date", type="string", format="date")
     *                 ),
     *                 @OA\Property(
     *                     property="area",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="range_course", type="array", @OA\Items(type="string")),
     *                     @OA\Property(property="area_id", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     )
     * )
     */
    public function show($id)
    {
        $token = request()->header('Authorization');
        if (!$token) {
            return response()->json([
                "error" => "Resource not found",
            ], 404);
        }

        $result = ResponsableService::decodeJWT($token);

        if ($result && isset($result['errors']) && !empty($result['errors'])) {
            return response()->json([
                'errors' => $result['errors'],
            ], 401);
        }

        $payload = $result['payload'];

        if (!$payload) {
            return response()->json([
                "error" => "Resource not found",
            ], 404);
        }

        try {
            $inscription = $this->inscriptionService->getInscriptionById($id, $payload['sub'], $payload['code']);
            return response()->json([
                'data' => $inscription,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Store a newly created inscription.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/inscriptions",
     *     tags={"Inscriptions"},
     *     summary="Store a new inscription",
     *     description="Create a new inscription with the provided data",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Data for creating an inscription",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"olympic_id", "legal_tutor", "responsable", "competitor"},
     *             @OA\Property(property="olympic_id", type="integer", description="The ID of the Olympic event", example=1),
     *             @OA\Property(property="legal_tutor", type="object",
     *                 description="Legal tutor information",
     *                 required={"ci", "ci_expedition", "names", "last_names", "birthdate", "email", "phone_number"},
     *                 @OA\Property(property="ci", type="integer", example=7890123),
     *                 @OA\Property(property="ci_expedition", type="string", example="Santa Cruz"),
     *                 @OA\Property(property="names", type="string", example="Maria Garcia"),
     *                 @OA\Property(property="last_names", type="string", example="Rodriguez Gomez"),
     *                 @OA\Property(property="birthdate", type="date", example="1965-11-24"),
     *                 @OA\Property(property="email", type="string", format="email", example="maria.garcia@example.com"),
     *                 @OA\Property(property="phone_number", type="string", pattern="+591 [0-9]{8}", example="+591 78901234")
     *             ),
     *             @OA\Property(property="responsable", type="object",
     *                 description="Responsable information",
     *                 required={"ci", "ci_expedition", "names", "last_names", "birthdate", "email", "phone_number"},
     *                 @OA\Property(property="ci", type="integer", example=7890123),
     *                 @OA\Property(property="ci_expedition", type="string", example="Santa Cruz"),
     *                 @OA\Property(property="names", type="string", example="Maria Garcia"),
     *                 @OA\Property(property="last_names", type="string", example="Rodriguez Gomez"),
     *                 @OA\Property(property="birthdate", type="date", example="1965-11-24"),
     *                 @OA\Property(property="email", type="string", format="email", example="maria.garcia@example.com"),
     *                 @OA\Property(property="phone_number", type="string", pattern="+591 [0-9]{8}", example="+591 78901234")
     *             ),
     *             @OA\Property(property="competitor", type="object",
     *                 description="Competitor information",
     *                 required={"ci", "ci_expedition", "names", "last_names", "birthdate", "email", "phone_number", "school_data", "selected_areas"},
     *                 @OA\Property(property="ci", type="integer", example=9387321),
     *                 @OA\Property(property="ci_expedition", type="string", example="Cochabamba"),
     *                 @OA\Property(property="names", type="string", example="Juan Perez"),
     *                 @OA\Property(property="last_names", type="string", example="Gonzalez Lopez"),
     *                 @OA\Property(property="birthdate", type="date", example="2020-05-18"),
     *                 @OA\Property(property="email", type="string", format="email", example="juan2.perez@example.com"),
     *                 @OA\Property(property="phone_number", type="string", pattern="+591 [0-9]{8}", example="+591 67834512"),
     *                 @OA\Property(property="school_data", type="object",
     *                     description="School data information",
     *                     required={"name", "department", "province", "course"},
     *                     @OA\Property(property="name", type="string", example="Colegio San Jose"),
     *                     @OA\Property(property="department", type="string", example="Cochabamba"),
     *                     @OA\Property(property="province", type="string", example="Cercado"),
     *                     @OA\Property(property="course", type="string", example="3ro Primaria")
     *                 ),
     *                 @OA\Property(property="selected_areas", type="array",
     *                     description="Selected areas information",
     *                     @OA\Items(
     *                         required={"area_id", "category_id"},
     *                         @OA\Property(property="area_id", type="integer", example=1,),
     *                         @OA\Property(property="category_id", type="integer", example=1,),
     *                         @OA\Property(property="academic_tutor", type="object",
     *                             description="Academic tutor information",
     *                             @OA\Property(property="ci", type="integer", example=4567890),
     *                             @OA\Property(property="ci_expedition", type="string", example="Cochabamba"),
     *                             @OA\Property(property="names", type="string", example="Carlos Sanchez"),
     *                             @OA\Property(property="last_names", type="string", example="Lopez Perez"),
     *                             @OA\Property(property="birthdate", type="date", example="1972-03-12"),
     *                             @OA\Property(property="email", type="string", format="email", example="carlos.sanchez@example.com"),
     *                             @OA\Property(property="phone_number", type="string", pattern="+591 [0-9]{8}", example="+591 89012345")
     *                         )
     *                     )
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inscription created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Data saved successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object",
     *                     description="Detailed validation errors for each field that failed validation.",
     *             @OA\Property(property="legal_tutor.ci", type="array", @OA\Items(type="string", example="The legal tutor ci field is required.")),
     *             @OA\Property(property="responsable.email", type="array", @OA\Items(type="string", example="The responsable email must be a valid email address."))
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object",
     *                 description="Detailed error message for internal server errors.",
     *                 @OA\Property(property="message", type="string", example="Algo salió mal")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {

        $validator = Validator::make(
            $request->all(),
            InscriptionsValidator::rules(),
            InscriptionsValidator::messages()
        );


        if ($validator->fails()) {
            Log::error($validator->errors());
            return response()->json([
                "errors" => $validator->errors(),
            ], 422);
        }

        $body = $validator->validated();



        $result = $this->inscriptionService->createInscription($body);

        if ($result && isset($result['errors']) && !empty($result['errors'])) {
            return response()->json([
                'errors' => $result['errors'],
            ], 409);
        }

        return response()->json([
            "data" => $result['data'] + ['body' => $body],
        ], 201);
    }



    /**
     * Update the specified inscription in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * @OA\Put(
     *     path="/inscriptions/{id}",
     *     tags={"Inscriptions"},
     *     summary="Update an inscription",
     *     description="Updates an inscription",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="other_data", type="string", example="Some other data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inscription not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
    }

    /**
     * Remove the specified inscription from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * @OA\Delete(
     *     path="/inscriptions/{id}",
     *     tags={"Inscriptions"},
     *     summary="Delete an inscription",
     *     description="Deletes an inscription",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful response"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inscription not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function destroy($id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
    }
}
