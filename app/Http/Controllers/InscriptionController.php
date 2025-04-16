<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\InscriptionService;
use App\Validators\InscriptionsValidator;

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
     * Display a listing of the inscriptions.
     *
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
     *     path="/inscriptions",
     *     tags={"Inscriptions"},
     *     summary="Get all inscriptions",
     *     description="Returns a list of all inscriptions",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Example"),
     *                 @OA\Property(property="email", type="string", example="example@example.com"),
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function index()
    {
        $inscriptions = $this->inscriptionService->getInscriptions();
        return response()->json([
            "data" => $inscriptions,
        ]);
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
     *             required={"legal_tutor", "responsable", "competitor"},
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
     *                         @OA\Property(property="area_id", type="integer", example=1,),
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

    /**
     * Display the specified inscription.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
     *     path="/inscriptions/{id}",
     *     tags={"Inscriptions"},
     *     summary="Get an inscription by ID",
     *     description="Returns an inscription by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Example"),
     *                 @OA\Property(property="email", type="string", example="example@example.com")
     *             )
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
    public function show($id)
    {
        return response()->json([
            "message" => "Not Implemented"
        ]);
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
