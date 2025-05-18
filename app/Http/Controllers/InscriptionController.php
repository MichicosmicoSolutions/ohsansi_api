<?php

namespace App\Http\Controllers;

use App\Enums\InscriptionStatus;
use App\Models\Accountables;
use App\Models\Areas;
use App\Models\Categories;
use App\Models\Inscriptions;
use App\Models\LegalTutors;
use App\Models\OlympiadAreas;
use App\Models\Olympiads;
use App\Models\PersonalData;
use App\Models\Schools;
use App\Models\SelectedAreas;
use App\Models\Teachers;
use App\Validators\SchoolDataValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\InscriptionService;
use App\Services\ResponsableService;
use Illuminate\Support\Facades\Validator;
use App\Validators\InscriptionsValidator;
use App\Validators\PersonalDataValidator;
use App\Validators\SelectedAreaValidator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToArray;

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
        } catch (Exception $e) {
            return response()->json([
                'errors' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *      path="/olympiads/{olympiadId}/inscriptions/init",
     *      operationId="initOlympiadInscription",
     *      tags={"Inscriptions"},
     *      summary="Initialize or retrieve competitor data for an olympiad inscription",
     *      description="Checks if a person with the given CI and birthdate exists. If found, returns their data including any existing inscription details for the specified olympiad.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="olympiadId",
     *          in="path",
     *          required=true,
     *          description="ID of the Olympiad",
     *          @OA\Schema(type="integer", format="int64", example=1)
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Competitor identification data",
     *          @OA\JsonContent(
     *              required={"ci", "birthdate"},
     *              type="object",
     *              @OA\Property(property="ci", type="integer", description="Competitor's Cédula de Identidad", example=1234567),
     *              @OA\Property(property="birthdate", type="string", format="date", description="Competitor's birthdate (YYYY-MM-DD)", example="2005-08-15")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Competitor data found successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                 property="data",
     *                 description="Competitor's personal and inscription data",
     *                 type="object",
     *                 ref="#/components/schemas/CompetitorPersonalData",
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Competitor data not found for the given CI and birthdate",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="details", type="string", example="Personal data not found")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error (e.g., missing or invalid input)",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 description="Object containing validation errors",
     *                 @OA\Property(property="ci", type="array", @OA\Items(type="string"), example={"The ci field is required."}),
     *                 @OA\Property(property="birthdate", type="array", @OA\Items(type="string"), example={"The birthdate field must be a valid date."})
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized (e.g., invalid or missing token)",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="errors", type="string", example="Unauthorized")
     *          )
     *      )
     * )
     */
    public function initInscription($olympiadId, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ci' => 'required|integer',
            'birthdate' => 'required|date',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }


        $person = PersonalData::with([
            'inscription' => function ($query) use ($olympiadId) {
                $query->where('olympiad_id', $olympiadId);
            },
            'inscription.school',
            'inscription.legalTutor',
            'inscription.legalTutor.personalData',
            'inscription.selected_areas',
            'inscription.selected_areas.teacher',
            'inscription.selected_areas.teacher.personalData',
            'inscription.accountable',
            'inscription.accountable.personalData',
        ])->where('ci', $request->ci)
            ->where('birthdate', $request->birthdate)->first();

        if (!$person) {
            $person = new PersonalData();
            $person->ci = $request->ci;
            $person->birthdate = $request->birthdate;
            $person->save();
        }


        $responseData = $person->toArray();
        // Add helper flags
        $responseData['is_accountable'] = $person->isAccountable();
        $responseData['is_competitor'] = $person->isCompetitor();
        $responseData['is_tutor'] = $person->isTutor();
        $responseData['is_teacher'] = $person->isTeacher();


        return response()->json([
            'data' => $responseData,
        ]);
    }


    /**
     * @OA\Post(
     *      path="/olympiads/{olympiadId}/inscriptions",
     *      operationId="storeCompetitor",
     *      tags={"Inscriptions"},
     *      summary="Store a new competitor",
     *      description="Returns the newly created inscription",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="olympiadId",
     *          description="Olympiad ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/PersonalDataRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object", ref="#/components/schemas/CompetitorPersonalData")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="errors", type="object", description="Detailed error message for bad requests.",
     *                 @OA\Property(property="param_name", type="string", example="Algo salió mal") 
     *             )
     *          )
     *      )
     * )
     */
    public function storeCompetitor($olympiadId, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            PersonalDataValidator::rules(),
            PersonalDataValidator::messages(),
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $olympiad = Olympiads::find($olympiadId);
        if (!$olympiad) {
            return response()->json([
                'errors' => ['details' => 'Olympiad not found'],
            ], 404);
        }

        DB::beginTransaction();
        try {
            $person = PersonalData::firstOrCreate(
                ['ci' => $request->ci],
                $request->all()
            );

            if (!$person->isCompetitor() && ($person->isTutor() || $person->isAccountable() || $person->isTeacher())) {
                DB::rollBack();
                return response()->json([
                    "message" => "El CI proporcionado yá está registrado pero no pertenece a un competidor",
                    "data" => $person
                ], 409);
            }

            PersonalData::where('ci', $request->ci)->update($request->all());

            Inscriptions::firstOrCreate(
                [
                    'competitor_data_id' => $person->id,
                    'olympiad_id' => $olympiadId
                ],
                [
                    'status' => InscriptionStatus::PENDING,
                    'competitor_data_id' => $person->id,
                    'olympiad_id' => $olympiadId,
                ]
            );
            DB::commit();
            $person = PersonalData::with([
                'inscription' => function ($query) use ($olympiadId) {
                    $query->where('olympiad_id', $olympiadId);
                },
                'accountable'
            ])->find($person->id);

            return response()->json([
                'message' => "Inscription created successfully",
                'data' => $person,
            ], 201);
        } catch (QueryException $qe) {
            DB::rollBack();
            return response()->json([
                'errors' => "Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde.",
            ], 409);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/olympiads/{olympiadId}/inscriptions/{inscriptionId}/schools",
     *     summary="Store a new competitor school data",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="olympiadId",
     *         in="path",
     *         description="ID of the olympiad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="inscriptionId",
     *         in="path",
     *         description="ID of the inscription",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SchoolData")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(ref="#/components/schemas/School")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for bad requests.",
     *                @OA\Property(property="param_name", type="string", example="Algo salió mal") 
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for not found resources.",
     *                @OA\Property(property="details", type="string", example="Algo salió mal") 
     *            )
     *         )
     *     ),
     * )
     */
    public function storeCompetitorSchool($olympiadId, $inscriptionId, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            SchoolDataValidator::rules(),
            SchoolDataValidator::messages()
        );
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        $olympiad = Olympiads::find($olympiadId);
        $inscription = Inscriptions::find($inscriptionId);
        if (!$inscription) {
            return response()->json([
                'errors' => ['details' => 'Inscripción no encontrada.'],
            ], 404);
        }
        if (!$olympiad) {
            return response()->json([
                'errors' => ['details' => 'Evento olímpico no encontrado.'],
            ], 404);
        }
        $school = Schools::firstOrCreate(
            ['name' => $request->input('name')],
            $request->all()
        );
        $inscription->school()->associate($school);
        $inscription->save();

        return response()->json([
            'message' => 'Escuela creada con éxito.',
            'data' => $school,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/olympiads/{olympiadId}/inscriptions/{inscriptionId}/tutors",
     *     summary="Store a new competitor tutor",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="olympiadId",
     *         in="path",
     *         description="ID of the olympiad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="inscriptionId",
     *         in="path",
     *         description="ID of the inscription",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PersonalData")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(ref="#/components/schemas/PersonalData")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for bad requests.",
     *                @OA\Property(property="param_name", type="string", example="Algo salió mal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for not found resources.",
     *                @OA\Property(property="details", type="string", example="Algo salió mal")
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Conflict",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for conflict.",
     *                @OA\Property(property="details", type="string", example="El competidor ya tiene un tutor legal asociado.")
     *            )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for internal server error.",
     *                @OA\Property(property="details", type="string", example="Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde.")
     *            )
     *         )
     *     ),
     * )
     */
    public function storeCompetitorTutor($olympiadId, $inscriptionId, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            PersonalDataValidator::rules(),
            PersonalDataValidator::messages()
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }
        $olympiad = Olympiads::find($olympiadId);
        if (!$olympiad) {
            return response()->json([
                'errors' => ['details' => 'Evento olímpico no encontrado.'],
            ], 404);
        }
        $inscription = Inscriptions::with([
            'legalTutor'
        ])->find($inscriptionId);

        if (!$inscription) {
            return response()->json([
                'errors' => ['details' => 'Inscripción no encontrada.'],
            ], 404);
        }

        if ($inscription->legalTutor) {
            return response()->json([
                'errors' => ['details' => 'El competidor ya tiene un tutor legal asociado.'],
            ], 409);
        }
        DB::beginTransaction();
        try {
            $personalData = PersonalData::firstOrCreate(
                ['ci' => $request->input('ci')],
                $request->all()
            );

            $legalTutor = LegalTutors::firstOrCreate(
                ['personal_data_id' => $personalData->id],
                ['personal_data_id' => $personalData->id]
            );
            $inscription->legalTutor()->associate($legalTutor);
            $inscription->save();

            DB::commit();
        } catch (QueryException $qe) {
            DB::rollBack();
            if ($qe->getCode() === '23505') {
                preg_match('/Key \((.*?)\)=\((.*?)\)/', $qe->getMessage(), $matches);
                $e = $matches[1];
                return response()->json([
                    'errors' => [
                        'details' => "el o los valores para {$e} están duplicados.",
                    ]
                ], 409);
            }
            return response()->json([
                "errors" => [
                    "details" => "Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde.",
                ]
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "errors" => [
                    "details" => "Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde.",
                ]
            ], 500);
        }

        return response()->json([
            'message' => 'Datos personales del competidor creados con éxito.',
            'data' => $personalData,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/olympiads/{olympiadId}/inscriptions/{inscriptionId}/selected-areas",
     *     summary="Create selected areas for the inscription",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="olympiadId",
     *         in="path",
     *         description="ID of the olympiad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="inscriptionId",
     *         in="path",
     *         description="ID of the inscription",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SelectedAreaRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(ref="#/components/schemas/SelectedArea")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for bad requests.",
     *                @OA\Property(property="param_name", type="string", example="Algo salió mal") 
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for not found resources.",
     *                @OA\Property(property="details", type="string", example="Algo salió mal") 
     *            )
     *         )
     *     ),
     * )
     */
    public function storeAssociatedArea($olympiadId, $inscriptionId, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'selected_areas' => 'required|array|min:1',
            ] + SelectedAreaValidator::rules('selected_areas.*'),
            [
                'selected_areas.required' => 'El campo selected_areas es obligatorio.',
            ] + SelectedAreaValidator::messages('selected_areas.*'),
        );

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $olympiad = Inscriptions::find($olympiadId);
        if (!$olympiad) {
            return response()->json([
                'errors' => ['details' => 'Olympiad not found'],
            ], 404);
        }
        $inscription = Inscriptions::with(['selected_areas'])->find($inscriptionId);
        if (!$inscription) {
            return response()->json([
                'errors' => ['details' => 'Inscription not found'],
            ], 404);
        }

        $registeredAreasCount = count($inscription->selected_areas);
        $selectedAreasCount = count($request->input('selected_areas'));
        if (2 - $registeredAreasCount < $selectedAreasCount) {
            return response()->json([
                'errors' => [
                    'selected_areas' => ['You can only select up to 2 areas per olympiad.']
                ]
            ], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($request->input('selected_areas') as $areaData) {
                $area = Areas::find($areaData['area_id']);
                $category = Categories::find($areaData['category_id']);
                if (!$area) {
                    $field = "selected_areas.*.area_id";
                    throw new Exception("$field, Area not found");
                }
                if (!$category) {
                    $field = 'selected_areas.*.category_id';
                    throw new Exception("$field, Category not found");
                }

                SelectedAreas::create([
                    'inscription_id' => $inscription->id,
                    'area_id' => $area->id,
                    'category_id' => $category->id
                ]);

                if (!isset($areaData['academic_tutor'])) {
                    continue;
                }

                $academic_tutor = $areaData["academic_tutor"];

                $personalData = PersonalData::firstOrCreate(
                    ['ci' => $academic_tutor['ci']],
                    $academic_tutor
                );

                $teacher = Teachers::firstOrCreate(
                    ['personal_data_id' => $personalData->id],
                    ['personal_data_id' => $personalData->id]
                );

                SelectedAreas::where('inscription_id', $inscription->id)
                    ->where('area_id', $area->id)
                    ->update([
                        'teacher_id' => $teacher->personal_data_id
                    ]);
            }
            DB::commit();
        } catch (QueryException $qe) {
            DB::rollBack();
            if ($qe->getCode() === '23505') {
                preg_match('/Key \((.*?)\)=\((.*?)\)/', $qe->getMessage(), $matches);
                $e = $matches[1];
                return response()->json([
                    'errors' => [
                        'details' => "los valores para {$e} están duplicados.",
                    ]
                ], 409);
            }
            return response()->json([
                "errors" => [
                    "details" => "Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde.",
                ]
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => [
                    'details' => $e->getMessage(),
                ]
            ], 500);
        }

        return response()->json([
            'message' => 'Áreas seleccionadas creadas con éxito.',
            'data' => $request->all(),
        ], 201);
    }


    /**
     * @OA\Post(
     *     path="/olympiads/{olympiadId}/inscriptions/{inscriptionId}/accountables",
     *     summary="Create accountable for the inscription",
     *     tags={"Inscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="olympiadId",
     *         in="path",
     *         description="ID of the olympiad",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="inscriptionId",
     *         in="path",
     *         description="ID of the inscription",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/PersonalDataRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(ref="#/components/schemas/PersonalData")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for bad requests.",
     *                @OA\Property(property="param_name", type="string", example="Algo salió mal")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="errors", type="object", description="Detailed error message for not found resources.",
     *                @OA\Property(property="details", type="string", example="Algo salió mal")
     *            )
     *         )
     *     ),
     * )
     */
    public function storeAccountable($olympiadId, $inscriptionId, Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            PersonalDataValidator::rules(),
            PersonalDataValidator::messages(),
        );
        if ($validator->fails()) {
            return response()->json(
                [
                    'errors' => ['details' => $validator->errors(),]
                ],
                422
            );
        }

        $olympiad = Olympiads::find($olympiadId);
        if (!$olympiad) {
            return response()->json([
                'errors' => [
                    'details' => 'Olympiad not found',
                ]
            ], 404);
        }

        $inscription = Inscriptions::with([
            'accountable',
        ])->find($inscriptionId);

        if (!$inscription) {
            return response()->json([
                'errors' => [
                    'details' => 'Inscription not found',
                ]
            ], 404);
        }

        if ($inscription->accountable) {
            return response()->json([
                'errors' => [
                    'details' => 'Inscription already has an accountable',
                ]
            ], 409);
        }

        DB::beginTransaction();
        try {
            $personalData = PersonalData::firstOrCreate(
                ['ci' => $request->input('ci')],
                $request->all()
            );
            $accountable = Accountables::firstOrCreate(
                ['personal_data_id' => $personalData->id],
                ['personal_data_id' => $personalData->id]
            );
            $inscription->accountable()->associate($accountable);
            $inscription->save();
            DB::commit();
            return response()->json([
                'message' => 'Accountable created successfully',
                'data' => $personalData,
            ], 201);
        } catch (QueryException $qe) {
            DB::rollBack();
            if ($qe->getCode() === '23505') {
                preg_match('/Key \((.*?)\)=\((.*?)\)/', $qe->getMessage(), $matches);
                $e = $matches[1];
                return response()->json([
                    'errors' => [
                        'details' => "el o los valores para {$e} están duplicados.",
                    ]
                ], 409);
            }

            return response()->json([
                "errors" => [
                    "details" => "Algo salió mal al procesar tu solicitud. Por favor, intenta nuevamente más tarde.",
                ]
            ], 500);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "errors" => [
                    "details" => "Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde.",
                ]
            ], 500);
        }
    }
}
