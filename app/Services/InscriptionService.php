<?php

namespace App\Services;

use App\Enums\InscriptionStatus;
use App\Models\AcademicTutors;
use App\Models\Areas;
use App\Models\Categories;
use App\Models\Competitors;
use App\Models\Inscriptions;
use App\Models\LegalTutors;
use App\Models\Olympics;
use Illuminate\Support\Facades\DB;
use App\Models\PersonalData;
use App\Models\Schools;
use Illuminate\Support\Facades\Log;

class InscriptionService
{

    public function getInscriptions()
    {
        return Inscriptions::with([
            'competitor.school',
            'competitor.academicTutor.personalData',
            'competitor.legalTutor.personalData',
        ])->get();
    }

    public function createInscription($validatedData)
    {
        DB::beginTransaction();

        $currentDate = now();
        $olympic = Olympics::where('start_date', '<=', $currentDate)
            ->where('end_date', '>=', $currentDate)
            ->first();

        if (!$olympic) {
            return response()->json(['error' => 'No se encontró la olimpiada activa'], 404);
        }

        try {

            $legalTutorData = PersonalData::where('ci', $validatedData['legal_tutor']['ci'])->first();
            if (!$legalTutorData) {
                $legalTutorData = PersonalData::create([
                    'ci' => $validatedData['legal_tutor']['ci'],
                    'ci_expedition' => $validatedData['legal_tutor']['ci_expedition'],
                    'names' => $validatedData['legal_tutor']['names'],
                    'last_names' => $validatedData['legal_tutor']['last_names'],
                    'birthdate' => $validatedData['legal_tutor']['birthdate'],
                    'email' => $validatedData['legal_tutor']['email'],
                    'phone_number' => $validatedData['legal_tutor']['phone_number'],
                ]);
            }
            $legalTutor = LegalTutors::where('personal_data_id', $legalTutorData->id)->first();
            if (!$legalTutor) {
                $legalTutor = LegalTutors::create(['personal_data_id' => $legalTutorData->id]);
            }


            $academicTutorData = PersonalData::where('ci', $validatedData['academic_tutor']['ci'])->first();
            if (!$academicTutorData) {
                $academicTutorData = PersonalData::create([
                    'ci' => $validatedData['academic_tutor']['ci'],
                    'ci_expedition' => $validatedData['academic_tutor']['ci_expedition'],
                    'names' => $validatedData['academic_tutor']['names'],
                    'last_names' => $validatedData['academic_tutor']['last_names'],
                    'birthdate' => $validatedData['academic_tutor']['birthdate'],
                    'email' => $validatedData['academic_tutor']['email'],
                    'phone_number' => $validatedData['academic_tutor']['phone_number'],
                ]);
            }
            $academicTutor = AcademicTutors::where('personal_data_id', $academicTutorData->id)->first();
            if (!$academicTutor) {
                $academicTutor = AcademicTutors::create(['personal_data_id' => $academicTutorData->id]);
            }

            $inscriptions = [];
            foreach ($validatedData['competitors'] as $competitorData) {

                $school = Schools::create([
                    'name' => $competitorData['school_data']['name'],
                    "department" => $competitorData['school_data']['department'],
                    'province' => $competitorData['school_data']['province'],
                ]);

                // Check if personal data already exists
                $personalData = PersonalData::where('ci', $competitorData['ci'])->first();
                if (!$personalData) {
                    $personalData = PersonalData::create([
                        "ci" => $competitorData['ci'],
                        "ci_expedition" => $competitorData['ci_expedition'],
                        "names" => $competitorData['names'],
                        "last_names" => $competitorData['last_names'],
                        "birthdate" => $competitorData['birthdate'],
                        "email" => $competitorData['email'],
                        "phone_number" => $competitorData['phone_number'],
                    ]);
                }

                $competitor = Competitors::where('personal_data_id', $personalData->id)->first();
                if (!$competitor) {
                    $competitor = Competitors::create([
                        "course" => $competitorData['school_data']['course'],
                        'school_id' => $school->id,
                        'legal_tutor_id' => $legalTutor->id,
                        'academic_tutor_id' => $academicTutor->id,
                        'personal_data_id' => $personalData->id,
                    ]);
                }

                $selectedAreas = $competitorData['selected_areas'];
                foreach ($selectedAreas as $areaId) {
                    $area = Areas::where('id', $areaId)->first();
                    $category = Categories::where('area_id', 6)
                        ->whereRaw("range_course::jsonb @> ?", ['["3ro Secundaria"]'])
                        ->first();


                    $inscription = Inscriptions::create([
                        'competitor_id' => $competitor->id,
                        'olympic_id' => $olympic->id,
                        'area_id' => $area->id,
                        'category_id' => $category->id,
                        'status' => InscriptionStatus::PENDING,
                    ]);
                }
                $inscriptions[] = $inscription;
            }

            DB::commit();

            return response()->json([
                "message" => "Data validated successfully",
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {

            DB::rollBack();
            if ($e->getCode() === '23505') {
                // Assuming the unique constraint is on the email field
                if (strpos($e->getMessage(), 'email') !== false) {
                    // Use a regular expression to extract the email from the error message
                    preg_match('/Key \((.*?)\)=\((.*?)\)/', $e->getMessage(), $matches);
                    $email = isset($matches[2]) ? $matches[2] : 'desconocido';

                    return response()->json([
                        "error" => "El correo electrónico {$email} ya está en uso. Por favor, verifica tus datos e intenta nuevamente.",
                    ], 400);
                }
            }

            return response()->json([
                "error" => "Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde. Error: " . $e->getMessage() . " Línea: " . $e->getLine(),
            ], 500);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                "error" => "Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde. Error: " . $e->getMessage() . " Línea: " . $e->getLine(),
            ], 500);
        }
    }
}
