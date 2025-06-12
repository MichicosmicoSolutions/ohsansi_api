<?php

namespace App\Http\Controllers;

use App\Enums\InscriptionStatus;
use App\Models\Accountables;
use App\Models\Areas;
use App\Models\Categories;
use App\Models\Inscriptions;
use App\Models\LegalTutors;
use App\Models\Olympiads;
use App\Models\PersonalData;
use App\Models\Schools;
use App\Models\SelectedAreas;
use App\Models\Teachers;
use App\Validators\SchoolDataValidator;
use Illuminate\Http\Request;
use App\Services\InscriptionService;
use App\Services\ResponsableService;
use Illuminate\Support\Facades\Validator;
use App\Validators\PersonalDataValidator;
use App\Validators\SelectedAreaValidator;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OlympicInscriptionImport;
use App\Models\BoletaDePago;
use App\Models\OlympiadAreas;
use App\Services\InscriptionExcelService;

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

    public function getFormData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ci' => 'required|string',
            'birthdate' => 'required|date',
            'olympicId' => 'required|integer|exists:olympiads,id',
            'type' => 'required|in:single,multiple',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ci = $request->input('ci');
        $birthdate = $request->input('birthdate');
        $olympiadId = $request->input('olympicId');
        $type = $request->input('type');
        $identifier = $ci . '|' . $birthdate;
        $groupIdentifier = $ci . '|' . $birthdate . '|' . $olympiadId;

        if (empty($identifier)) {
            return response()->json(['error' => 'El identificador no puede ser nulo'], 422);
        }
        if (!in_array($type, ['single', 'multiple'])) {
            return response()->json(['error' => 'El tipo es invalido'], 422);
        }
        // Check if the inscription exists and is in draft status


        if ($type === 'single') {
            $inscription = Inscriptions::with([
                'school',
                'legalTutor',
                'legalTutor.personalData',
                'accountable',
                'accountable.personalData',
                'selected_areas',
                'selected_areas.teacher',
                'selected_areas.teacher.personalData',
                'competitor_data',
                'competitor_data.personalData'
            ])
                ->where('identifier', $identifier)
                ->where('olympiad_id', $olympiadId)
                ->first();

            if (!$inscription) {
                return response()->json([
                    'error' => 'Inscripción no encontrada.',
                ], 400);
            }

            if ($inscription->status !== InscriptionStatus::DRAFT) {
                return response()->json([
                    'error' => 'La inscripción no está en estado borrador.',
                ], 409);
            }

            $olympiad = Olympiads::find($olympiadId);
            $currentStep = 0;

            if ($inscription->competitor_data) {
                $currentStep = 3;
            } elseif ($inscription->student) {
                $currentStep = 2;
            } elseif ($inscription->school) {
                $currentStep = 1;
            }

            return response()->json([
                'message' => 'Datos del formulario cargados correctamente.',
                'data' => [
                    'step' => $currentStep,
                    'inscription' => $inscription,
                    'olympiad' => [
                        'id' => $olympiad->id,
                        'name' => $olympiad->name,
                        'price' => $olympiad->price,
                    ],
                    'type' => $type,
                ],
            ]);
        } else if ($type === 'multiple') {
            $inscriptions = Inscriptions::with([
                'school',
                'legalTutor',
                'legalTutor.personalData',
                'accountable',
                'accountable.personalData',
                'selected_areas',
                'selected_areas.teacher',
                'selected_areas.teacher.personalData',
                'competitor_data',
                'competitor_data.personalData'
            ])
                ->where('identifier', $groupIdentifier)
                ->where('olympiad_id', $olympiadId)
                ->get();

            if ($inscriptions->isEmpty()) {
                return response()->json([
                    'error' => 'No se encontraron inscripciones.',
                ], 404);
            }

            foreach ($inscriptions as $inscription) {
                if ($inscription->status !== InscriptionStatus::DRAFT) {
                    return response()->json([
                        'error' => 'Las inscripciones no están en estado borrador.',
                    ], 409);
                }
            }

            $currentStep = 0;
            if ($inscriptions->first()->legalTutor) {
                $currentStep = 2;
            } elseif ($inscriptions->first()->school) {
                $currentStep = 1;
            }

            $olympiad = Olympiads::find($olympiadId);

            return response()->json([
                'message' => 'Datos del formulario cargados correctamente.',
                'data' => [
                    'step' => $currentStep,
                    'inscriptions' => $inscriptions,
                    'olympiad' => [
                        'id' => $olympiad->id,
                        'name' => $olympiad->name,
                        'price' => $olympiad->price,
                    ],
                    'type' => $type,
                ],
            ]);
        }
        return response()->json(['error' => 'Invalid type provided'], 422);
    }

    public function storeOlympic(Request $request)
    {
        $identityHeader = $request->header('Identity');
        $step = $request->header('Step');

        if (!$identityHeader) {
            return response()->json(['error' => 'Missing Identity header'], 400);
        }

        $identity = json_decode($identityHeader, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in Identity header'], 400);
        }

        if (!$step) {
            return response()->json(['error' => 'Missing Step header'], 400);
        }

        if (!is_numeric($step) || (int)$step < 1) {
            return response()->json(['error' => 'Invalid Step value'], 422);
        }

        $validator = Validator::make($identity, [
            'ci' => 'required|string|max:20',
            'birthdate' => 'required|date',
            'olympicId' => 'required|integer|exists:olympiads,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $step = (int)$step;
        $ci = $identity['ci'];
        $birthdate = $identity['birthdate'];
        $olympiadId = $identity['olympicId'];
        $identifier = $ci . '|' . $birthdate . '|' . $olympiadId;

        $verifyInscription = Inscriptions::where('identifier', $identifier)
            ->where('olympiad_id', $olympiadId)
            ->first();
        if ($verifyInscription && $verifyInscription->status !== InscriptionStatus::DRAFT) {
            return response()->json([
                'error' => 'La inscripción ya existe y no está en estado borrador.',
            ], 409);
        }

        if ($step === 1) {
            $validator = Validator::make($request->all(), [
                'school' => 'required|array',
                'school.name' => 'required|string|max:255',
                'school.department' => 'required|string|max:255',
                'school.province' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $schoolData = $validator->validated()['school'];

            $school = Schools::updateOrCreate(
                ['name' => $schoolData['name']],
                [
                    'department' => $schoolData['department'],
                    'province' => $schoolData['province'],
                ]
            );

            $inscription = Inscriptions::firstOrNew([
                'identifier' => $identifier,
                'olympiad_id' => $olympiadId,
            ]);

            $inscription->status = InscriptionStatus::DRAFT;
            $inscription->school_id = $school->id;
            $inscription->identifier = $identifier;
            $inscription->olympiad_id = $olympiadId;

            $inscription->save();

            return response()->json([
                'message' => 'Escuela registrada y asociada a la inscripción correctamente.',
                'data' => [
                    'school' => $school,
                    'inscription' => $inscription,
                ],
            ]);
        } else if ($step === 2) {
            $validator = Validator::make($request->all(), [
                'student' => 'required|array',
                'student.data' => 'required|array',
                'student.data.ci' => 'required|string|max:20',
                'student.data.birthdate' => 'required|date',
                'student.data.ci_expedition' => 'nullable|string|max:10',
                'student.data.names' => 'nullable|string|max:255',
                'student.data.last_names' => 'nullable|string|max:255',
                'student.data.email' => 'nullable|email|max:255',
                'student.data.phone_number' => 'nullable|string|max:20',
                'student.data.gender' => 'nullable|string|in:M,F,O',

                'legal_tutor' => 'required|array',
                'legal_tutor.ci' => 'required|string|max:20',
                'legal_tutor.birthdate' => 'required|date',
                'legal_tutor.ci_expedition' => 'nullable|string|max:10',
                'legal_tutor.names' => 'nullable|string|max:255',
                'legal_tutor.last_names' => 'nullable|string|max:255',
                'legal_tutor.email' => 'nullable|email|max:255',
                'legal_tutor.phone_number' => 'nullable|string|max:20',
                'legal_tutor.gender' => 'nullable|string|in:M,F,O',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();

            $studentData = $data['student']['data'];
            $student = PersonalData::updateOrCreate(
                [
                    'ci' => $studentData['ci'],
                ],
                $studentData
            );

            $tutorData = $data['legal_tutor'];
            $legalTutor = PersonalData::updateOrCreate(
                [
                    'ci' => $tutorData['ci'],
                ],
                $tutorData
            );

            $legalTutorRelation = LegalTutors::firstOrCreate([
                'personal_data_id' => $legalTutor->id
            ]);

            $inscription = Inscriptions::updateOrCreate(
                [
                    'identifier' => $identifier,
                    'olympiad_id' => $olympiadId,
                ],
                [
                    'status' => InscriptionStatus::DRAFT,
                    'legal_tutor_id' => $legalTutorRelation->personal_data_id,
                    'competitor_data_id' => $student->id,
                ]
            );

            return response()->json([
                'message' => 'Competidor y tutor legal guardados exitosamente.',
                'data' => [
                    'student' => $student,
                    'legal_tutor' => $legalTutor,
                    'inscription' => $inscription,
                ],
            ]);
        } else if ($step === 3) {
            $validator = Validator::make($request->all(), [
                'selected_areas' => 'required|array|min:1|max:2',
                'selected_areas.*.data.area_id' => 'required|integer|exists:areas,id',
                'selected_areas.*.data.category_id' => 'required|integer|exists:categories,id',

                'selected_areas.*.teacher.ci' => 'nullable|string|max:20',
                'selected_areas.*.teacher.birthdate' => 'nullable|date',
                'selected_areas.*.teacher.ci_expedition' => 'nullable|string|max:10',
                'selected_areas.*.teacher.names' => 'nullable|string|max:255',
                'selected_areas.*.teacher.last_names' => 'nullable|string|max:255',
                'selected_areas.*.teacher.email' => 'nullable|email|max:255',
                'selected_areas.*.teacher.phone_number' => 'nullable|string|max:20',
                'selected_areas.*.teacher.gender' => 'nullable|string|in:M,F,O',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();
            $selectedAreas = $validated['selected_areas'];

            // Obtener la inscripción asociada
            $inscription = Inscriptions::where('identifier', $identifier)
                ->where('olympiad_id', $olympiadId)
                ->first();

            if (!$inscription) {
                return response()->json(['error' => 'Inscripción no encontrada para el participante y la olimpiada.'], 404);
            }

            $results = [];

            DB::beginTransaction();

            foreach ($selectedAreas as $entry) {
                $areaData = $entry['data'];
                $teacherData = $entry['teacher'] ?? null;

                $teacherId = null;

                if ($teacherData && isset($teacherData['ci'])) {
                    $teacher = PersonalData::updateOrCreate(
                        ['ci' => $teacherData['ci']],
                        $teacherData
                    );
                    $teacherId = $teacher->id;
                    $teacherRelation = Teachers::firstOrCreate([
                        'personal_data_id' => $teacherId,
                    ]);
                    $teacherId = $teacherRelation->personal_data_id;
                }

                $studentId = $inscription->competitor_data_id;
                $olympiadInscriptions = Inscriptions::where('competitor_data_id', $studentId)
                    ->where('olympiad_id', $olympiadId)
                    ->get();
                $areas = SelectedAreas::whereIn('inscription_id', $olympiadInscriptions->pluck('id'))
                    ->count();
                if ($areas >= 2) {
                    return response()->json([
                        'error' => 'El estudiante ya tiene 2 áreas seleccionadas para esta olimpiada.',
                    ], 422);
                }


                $inscription = Inscriptions::where('identifier', $identifier)
                    ->where('olympiad_id', $olympiadId)
                    ->first();

                $currentSelectedAreasCount = SelectedAreas::where('inscription_id', $inscription->id)->count();
                if ($currentSelectedAreasCount >= 2) {
                    return response()->json([
                        'error' => 'No puedes seleccionar más de 2 áreas.',
                    ], 422);
                }

                // Crear o actualizar en selected_areas
                $selectedArea = SelectedAreas::updateOrCreate(
                    [
                        'inscription_id' => $inscription->id,
                        'area_id' => $areaData['area_id'],
                    ],
                    [
                        'category_id' => $areaData['category_id'],
                        'teacher_id' => $teacherId,
                        'paid_at' => null, // O colocar valor si ya se pagó
                    ]
                );

                $results[] = [
                    'area_id' => $selectedArea->area_id,
                    'category_id' => $selectedArea->category_id,
                    'teacher_id' => $teacherId,
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Áreas seleccionadas registradas exitosamente.',
                'data' => $results,
            ]);
        } else if ($step === 4) {
            $validator = Validator::make($request->all(), [
                'accountable' => 'required|array',
                'accountable.ci' => 'required|string|max:20',
                'accountable.birthdate' => 'required|date',
                'accountable.ci_expedition' => 'nullable|string|max:10',
                'accountable.names' => 'nullable|string|max:255',
                'accountable.last_names' => 'nullable|string|max:255',
                'accountable.email' => 'nullable|email|max:255',
                'accountable.phone_number' => 'nullable|string|max:20',
                'accountable.gender' => 'nullable|string|in:M,F,O',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $accountableData = $validator->validated()['accountable'];

            DB::beginTransaction();

            // Guardar o actualizar PersonalData del accountable
            $accountable = PersonalData::updateOrCreate(
                [
                    'ci' => $accountableData['ci'],
                ],
                $accountableData
            );

            $accountableRelation = Accountables::firstOrCreate([
                'personal_data_id' => $accountable->id,
            ]);

            // Obtener la inscripción asociada
            $inscription = Inscriptions::where('identifier', $identifier)
                ->where('olympiad_id', $olympiadId)
                ->first();

            if (!$inscription) {
                return response()->json(['error' => 'Inscripción no encontrada para el participante y la olimpiada.'], 404);
            }


            $olympiad = Olympiads::find($olympiadId);
            $randomNumber = rand(100000, 999999);

            $boleta = BoletaDePago::create([
                'numero_orden_de_pago' => $randomNumber,
                'ci' => $accountable->ci,
                'status' => 'pending',
                'nombre' => $accountable->names,
                'apellido' => $accountable->last_names,
                'fecha_nacimiento' => $accountable->birthdate,
                'cantidad' => 1,
                'concepto' => 'Inscripción Olimpiada: ' . $olympiad->name,
                'precio_unitario' => $olympiad->price,
                'importe' => $olympiad->price,
                'total' => $olympiad->price,
            ]);


            $inscription->accountable_id = $accountableRelation->personal_data_id;
            $inscription->status = InscriptionStatus::PENDING;
            $inscription->boleta_de_pago_id = $boleta->id;
            $inscription->save();

            DB::commit();

            return response()->json([
                'message' => 'Responsable de pago guardado correctamente.',
                'data' => [
                    'accountable' => $accountable,
                    'inscription' => $inscription,
                    "price" => $olympiad->price,
                    'boleta' => $boleta
                ],
            ]);
        }
    }

    public function storeOlympicMultiple(Request $request)
    {
        $identityHeader = $request->header('Identity');
        $step = $request->header('Step');

        if (!$identityHeader) {
            return response()->json(['error' => 'Missing Identity header'], 400);
        }

        $identity = json_decode($identityHeader, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in Identity header'], 400);
        }

        if (!$step) {
            return response()->json(['error' => 'Missing Step header'], 400);
        }

        if (!is_numeric($step) || (int)$step < 1) {
            return response()->json(['error' => 'Invalid Step value'], 422);
        }

        $step = (int)$step;
        $ci = $identity['ci'];
        $birthdate = $identity['birthdate'];
        $olympiadId = $identity['olympicId'];
        $identifier = $ci . '|' . $birthdate;
        $groupIdentifier = $identity['ci'] . '|' . $identity['birthdate'] . '|' . $olympiadId;

        $olympiad = Olympiads::find($olympiadId);
        if (!$olympiad) {
            return response()->json(['error' => 'Olympiad not found'], 404);
        }

        $verifyInscription = Inscriptions::where('identifier', $identifier)
            ->where('olympiad_id', $olympiadId)
            ->first();
        if ($verifyInscription && $verifyInscription->status !== InscriptionStatus::DRAFT) {
            return response()->json([
                'error' => 'La inscripción ya existe y no está en estado borrador.'
            ], 409);
        }

        if ($step === 1) {
            $validator = Validator::make($request->all(), [
                'school' => 'required|array',
                'school.name' => 'required|string|max:255',
                'school.department' => 'required|string|max:255',
                'school.province' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $schoolData = $validator->validated()['school'];


            // Crear o actualizar la escuela
            $school = Schools::updateOrCreate(
                ['name' => $schoolData['name']],
                [
                    'department' => $schoolData['department'],
                    'province' => $schoolData['province'],
                ]
            );

            $inscription = Inscriptions::firstOrNew([
                'identifier' => $identifier,
                'olympiad_id' => $olympiadId,

            ]);
            $inscription->status = InscriptionStatus::DRAFT;
            $inscription->school_id = $school->id;
            $inscription->identifier = $identifier;
            $inscription->olympiad_id = $olympiadId;
            $inscription->save();

            return response()->json([
                'message' => 'Escuela registrada correctamente para inscripción múltiple.',
                'data' => [
                    'school_id' => $school->id,
                    'school' => $school,
                ],
            ]);
        } else if ($step === 2) {
            $data = $request->json()->all();
            $validator = Validator::make($data, [
                '*.student.ci' => 'required|string|max:20',
                '*.student.birthdate' => 'required|date',
                '*.student.ci_expedition' => 'nullable|string|max:10',
                '*.student.names' => 'nullable|string|max:255',
                '*.student.last_names' => 'nullable|string|max:255',
                '*.student.email' => 'nullable|email|max:255',
                '*.student.phone_number' => 'nullable|string|max:20',
                '*.student.gender' => 'nullable|string|in:M,F,O',

                '*.legal_tutor.ci' => 'required|string|max:20',
                '*.legal_tutor.birthdate' => 'required|date',
                '*.legal_tutor.ci_expedition' => 'nullable|string|max:10',
                '*.legal_tutor.names' => 'nullable|string|max:255',
                '*.legal_tutor.last_names' => 'nullable|string|max:255',
                '*.legal_tutor.email' => 'nullable|email|max:255',
                '*.legal_tutor.phone_number' => 'nullable|string|max:20',
                '*.legal_tutor.gender' => 'nullable|string|in:M,F,O',

                '*.selected_areas' => 'required|array|min:1|max:2',
                '*.selected_areas.*.data.area_id' => 'required|integer|exists:areas,id',
                '*.selected_areas.*.data.category_id' => 'required|integer|exists:categories,id',

                '*.selected_areas.*.teacher.ci' => 'nullable|string|max:20',
                '*.selected_areas.*.teacher.birthdate' => 'nullable|date',
                '*.selected_areas.*.teacher.ci_expedition' => 'nullable|string|max:10',
                '*.selected_areas.*.teacher.names' => 'nullable|string|max:255',
                '*.selected_areas.*.teacher.last_names' => 'nullable|string|max:255',
                '*.selected_areas.*.teacher.email' => 'nullable|email|max:255',
                '*.selected_areas.*.teacher.phone_number' => 'nullable|string|max:20',
                '*.selected_areas.*.teacher.gender' => 'nullable|string|in:M,F,O',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $results = [];

            $baseInscription = Inscriptions::where('identifier', $identifier)
                ->where('olympiad_id', $olympiadId)
                ->first();

            if (!$baseInscription) {
                return response()->json(['error' => 'Base inscription not found'], 404);
            }

            $schoolId = $baseInscription->school_id;

            DB::beginTransaction();

            foreach ($data as $entry) {
                $studentData = $entry['student'];
                $tutorData = $entry['legal_tutor'];

                $student = PersonalData::updateOrCreate(
                    ['ci' => $studentData['ci'], 'birthdate' => $studentData['birthdate']],
                    $studentData
                );

                $tutor = PersonalData::updateOrCreate(
                    ['ci' => $tutorData['ci'], 'birthdate' => $tutorData['birthdate']],
                    $tutorData
                );

                LegalTutors::firstOrCreate(['personal_data_id' => $tutor->id]);

                $studentIdentifier = $student->ci . '|' . $student->birthdate . '|' . $olympiadId;

                $inscription = Inscriptions::updateOrCreate(
                    [
                        'identifier' => $studentIdentifier,
                        'olympiad_id' => $olympiadId,
                    ],
                    [
                        'group_identifier' => $groupIdentifier,
                        'school_id' => $schoolId,
                        'legal_tutor_id' => $tutor->id,
                        'competitor_data_id' => $student->id,
                        'status' => InscriptionStatus::DRAFT,
                    ]
                );

                $areaResults = [];

                foreach ($entry['selected_areas'] as $areaEntry) {
                    $areaData = $areaEntry['data'];
                    $teacherData = $areaEntry['teacher'] ?? null;

                    $teacherId = null;

                    if ($teacherData && !empty($teacherData['ci'])) {
                        $teacher = PersonalData::updateOrCreate(
                            ['ci' => $teacherData['ci']],
                            $teacherData
                        );
                        $teacherId = $teacher->id;
                        $teacherRelation = Teachers::firstOrCreate([
                            'personal_data_id' => $teacherId,
                        ]);
                        $teacherId = $teacherRelation->personal_data_id;
                    }

                    // Validar que el estudiante no haya seleccionado más de 2 áreas
                    $currentSelectedAreasCount = SelectedAreas::where('inscription_id', $inscription->id)->count();
                    if ($currentSelectedAreasCount >= 2) {
                        return response()->json([
                            'error' => 'No puedes seleccionar más de 2 áreas.',
                        ], 422);
                    }

                    SelectedAreas::updateOrCreate(
                        [
                            'inscription_id' => $inscription->id,
                            'area_id' => $areaData['area_id'],
                        ],
                        [
                            'category_id' => $areaData['category_id'],
                            'teacher_id' => $teacherId,
                            'paid_at' => null,
                        ]
                    );

                    $areaResults[] = [
                        'area_id' => $areaData['area_id'],
                        'category_id' => $areaData['category_id'],
                        'teacher_id' => $teacherId,
                    ];
                }

                $results[] = [
                    'student_ci' => $student->ci,
                    'inscription_id' => $inscription->id,
                    'areas' => $areaResults,
                ];
            }

            DB::commit();

            return response()->json([
                'message' => 'Paso 2 completado: estudiantes, tutores y áreas registradas.',
                'data' => $results,
            ]);
        } else if ($step === 3) {
            $validator = Validator::make($request->all(), [
                'accountable' => 'required|array',
                'accountable.ci' => 'required|string|max:20',
                'accountable.birthdate' => 'required|date',
                'accountable.ci_expedition' => 'nullable|string|max:10',
                'accountable.names' => 'nullable|string|max:255',
                'accountable.last_names' => 'nullable|string|max:255',
                'accountable.email' => 'nullable|email|max:255',
                'accountable.phone_number' => 'nullable|string|max:20',
                'accountable.gender' => 'nullable|string|in:M,F,O',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $accountableData = $request->input('accountable');

            DB::beginTransaction();

            // Crear o actualizar datos personales
            $personalData = PersonalData::updateOrCreate(
                [
                    'ci' => $accountableData['ci'],
                    'birthdate' => $accountableData['birthdate'],
                ],
                $accountableData
            );

            // Asegurar que exista en tabla `accountables`
            $accountable = Accountables::firstOrCreate([
                'personal_data_id' => $personalData->id,
            ]);

            // Obtener todas las inscripciones del grupo
            $inscriptions = Inscriptions::where('group_identifier', $groupIdentifier)
                ->where('olympiad_id', $olympiadId)
                ->get();

            if ($inscriptions->isEmpty()) {
                return response()->json(['error' => 'No hay inscripciones con ese agrupador'], 404);
            }

            foreach ($inscriptions as $inscription) {
                $inscription->accountable_id = $accountable->id;
                $inscription->status = InscriptionStatus::PENDING;
                $inscription->save();
            }

            $inscriptionsByGroup = Inscriptions::where('group_identifier', $groupIdentifier)
                ->where('olympiad_id', $olympiadId)
                ->get();
            $inscriptionNumberInGroup =  Inscriptions::where('group_identifier', $groupIdentifier)
                ->where('olympiad_id', $olympiadId)->count();


            $randomNumber = rand(100000, 999999);
            $boleta = BoletaDePago::create([
                'numero_orden_de_pago' => $randomNumber,
                'ci' => $accountable->ci,
                'status' => 'pending',
                'nombre' => $accountable->names,
                'apellido' => $accountable->last_names,
                'fecha_nacimiento' => $accountable->birthdate,
                'cantidad' => $inscriptionNumberInGroup,
                'concepto' => 'Inscripción Olimpiada: ' . $olympiad->name,
                'precio_unitario' => $olympiad->price,
                'importe' => $olympiad->price * $inscriptionNumberInGroup,
                'total' => $olympiad->price * $inscriptionNumberInGroup,
            ]);

            foreach ($inscriptionsByGroup as $inscription) {
                $inscription->boleta_de_pago_id = $boleta->id;
                $inscription->save();
            }

            // delete base inscription
            $baseInscription = Inscriptions::where('identifier', $identifier)
                ->where('olympiad_id', $olympiadId)
                ->first();
            if ($baseInscription) {
                $baseInscription->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Responsable de pago asociado a todas las inscripciones del grupo.',
                'data' => [
                    'accountable' => $personalData,
                    'inscriptions_updated' => $inscriptions->count(),
                    "boleta" => $boleta,
                ],
            ]);
        }
    }

    public function exportToExcel(Request $request)
    {
        $olympiadId  = $request->route('id');

        $olympiad = Olympiads::find($olympiadId);
        if (!$olympiad) {
            return response()->json(['error' => 'Olympiad not found'], 404);
        }

        $fileName = 'inscripciones_' . now()->timestamp . '.xlsx';
        $filePath = storage_path("app/{$fileName}");

        $areas = OlympiadAreas::where('olympiad_id', $olympiadId)->with('area')->with('category')->get();

        InscriptionExcelService::generateExcel($filePath, $areas);
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function importFromExcel(Request $request)
    {
        $olympiadId  = $request->route('id');

        $olympiad = Olympiads::find($olympiadId);
        if (!$olympiad) {
            return response()->json(['error' => 'Olympiad not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls',
            'schoolName' => 'required|string|max:255',
            'schoolDepartment' => 'required|string|max:255',
            'schoolProvince' => 'required|string|max:255',

            'accountableCi' => 'required|string|max:20',
            'accountableBirthdate' => 'required|date',
            'accountableCiExpedition' => 'nullable|string|max:10',
            'accountableNames' => 'nullable|string|max:255',
            'accountableLastNames' => 'nullable|string|max:255',
            'accountableEmail' => 'nullable|email|max:255',
            'accountablePhoneNumber' => 'nullable|string|max:20',
            'accountableGender' => 'nullable|string|in:M,F,O',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            DB::beginTransaction();
            $school = Schools::updateOrCreate(
                ['name' => $data['schoolName']],
                [
                    'department' => $data['schoolDepartment'],
                    'province' => $data['schoolProvince'],
                ]
            );

            // Crear o actualizar el responsable (PersonalData y Accountables)
            $accountable = PersonalData::updateOrCreate(
                ['ci' => $data['accountableCi'], 'birthdate' => $data['accountableBirthdate']],
                [
                    'ci_expedition' => $data['accountableCiExpedition'],
                    'names' => $data['accountableNames'],
                    'last_names' => $data['accountableLastNames'],
                    'email' => $data['accountableEmail'],
                    'phone_number' => $data['accountablePhoneNumber'],
                    'gender' => $data['accountableGender'],
                ]
            );

            $accountableRelation = Accountables::firstOrCreate([
                'personal_data_id' => $accountable->id,
            ]);

            // Procesar Excel (necesitas definir OlympicInscriptionImport)
            Excel::import(new OlympicInscriptionImport($school->id, $olympiadId, $accountableRelation->id), $request->file('file'));

            DB::commit();

            return response()->json([
                'message' => 'Importación completada correctamente',
                'data' => [
                    'school' => $school,
                    'accountable' => $accountable,
                ]
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al procesar la importación',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
