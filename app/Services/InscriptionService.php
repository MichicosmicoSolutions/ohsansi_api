<?php

namespace App\Services;

use App\Enums\InscriptionStatus;
use App\Models\{Competitors, Olympics, PersonalData, Schools, Areas, Categories, Inscriptions, LegalTutors, Accountables};
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class InscriptionService
{
    public function getInscriptions($ci, $code)
    {
        return Inscriptions::with([
            'competitor',
            'competitor.school',
            'competitor.accountable.personalData',
            'competitor.legalTutor.personalData',
            'olympic',
            'area',
            'category',
        ])->whereHas('competitor.accountable.personalData', function ($query) use ($ci) {
            $query->where('ci', $ci);
        })->whereHas('competitor', function ($query) use ($code) {
            $query->whereHas('accountable', function ($query) use ($code) {
                $query->where('code', $code);
            });
        })->get();
    }

    public function getInscriptionById($id, $ci, $code)
    {
        return Inscriptions::with([
            'competitor',
            'competitor.school',
            'competitor.accountable.personalData',
            'competitor.legalTutor.personalData',
            'olympic',
            'area',
            'category',
        ])->whereHas('competitor.accountable.personalData', function ($query) use ($ci) {
            $query->where('ci', $ci);
        })->whereHas('competitor', function ($query) use ($code) {
            $query->whereHas('accountable', function ($query) use ($code) {
                $query->where('code', $code);
            });
        })->findOrFail($id);
    }

    public function createInscription($validatedData)
    {
        DB::beginTransaction();

        try {
            $olympic = Olympics::findOrFail($validatedData['olympic_id']);

            if (!($olympic->status == "Publico")) {
                return ['errors' => ['olympic_id' => ['This Olympic is not active.']]];
            }

            $responsableData = $this->getOrCreatePersonalData($validatedData['accountable'], 'ci');
            $legalTutorData = $this->getOrCreatePersonalData($validatedData['legal_tutor'], 'ci');
            $competitorData = $this->getOrCreatePersonalData($validatedData['competitor'], 'ci');

            $school = Schools::create([
                'name' => $validatedData['competitor']['school_data']['name'],
                "department" => $validatedData['competitor']['school_data']['department'],
                'province' => $validatedData['competitor']['school_data']['province'],
            ]);

            $selectedAreas = $validatedData['competitor']['selected_areas'];


            $legalTutor = LegalTutors::firstOrCreate(
                ['personal_data_id' => $legalTutorData->id],
                [
                    'personal_data_id' => $legalTutorData->id,
                ]
            );

            $accountable = Accountables::firstOrCreate(
                ['personal_data_id' => $responsableData->id],
                [
                    'personal_data_id' => $responsableData->id,
                    'code' => $this->generateCode(),
                ]
            );

            $competitor = Competitors::where('personal_data_id', $competitorData->id)->first();
            if ($competitor) {
                $totalAreas = $competitor->inscriptions()->count();
                if ((2 - $totalAreas) == count($selectedAreas)) {
                    return ['errors' => ['competitor' => ['El competidor ya no puede inscribir más áreas']]];
                }
            } else {
                $competitor = Competitors::create([
                    'school_id' => $school->id,
                    'legal_tutor_id' => $legalTutor->id,
                    'personal_data_id' => $competitorData->id,
                    'accountable_id' => $accountable->id,
                    "course" => $validatedData['competitor']['school_data']['course'],
                ]);
            }

            foreach ($selectedAreas as $index => $selectedArea) {
                $area = Areas::find($selectedArea['area_id']);
                $category = Categories::find($selectedArea['category_id']);
                if (!$area) {
                    $fieldName = "competitor.selected_areas.{$index}.area_id";
                    return ['errors' => [$fieldName => ['Area no encontrada']]];
                }
                if (!$category) {
                    $fieldName = "competitor.selected_areas.{$index}.category_id";
                    return ['errors' => [$fieldName => ['Category no encontrada']]];
                }
                if (!in_array($competitor->course, $category->range_course)) {
                    $fieldName = 'competitor.school_data.course';
                    return ['errors' => [$fieldName => ['Course mismatch for category']]];
                }

                Inscriptions::create([
                    'competitor_id' => $competitor->id,
                    'olympic_id' => $olympic->id,
                    'area_id' => $area->id,
                    'category_id' => $category->id,
                    'status' => InscriptionStatus::PENDING,
                ]);
            }

            DB::commit();
            return ['data' => [
                "message" => "Data validated successfully",
                "code" => $accountable->code
            ]];
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->handleDatabaseError($e);
        } catch (Exception $e) {
            DB::rollBack();
            return ['errors' => [
                "error" => ["Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde. Error: " . $e->getMessage()],
            ]];
        }
    }

    private function generateCode()
    {
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8) . ' ';
        }
        $code = rtrim($code); // Remove the trailing space
        return $code;
    }

    private function getOrCreatePersonalData($data, $uniqueField = 'ci')
    {
        return PersonalData::firstOrCreate(
            [$uniqueField => $data[$uniqueField]],
            [
                'ci' => $data['ci'],
                'ci_expedition' => $data['ci_expedition'],
                'names' => $data['names'],
                'last_names' => $data['last_names'],
                'birthdate' => $data['birthdate'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
            ]
        );
    }

    private function handleDatabaseError(QueryException $e)
    {
        if ($e->getCode() === '23505') {
            preg_match('/Key \((.*?)\)=\((.*?)\)/', $e->getMessage(), $matches);
            $email = isset($matches[2]) ? $matches[2] : 'desconocido';
            return ['errors' => [
                "error" => ["El correo electrónico {$email} ya está en uso. Por favor, verifica tus datos e intenta nuevamente."],
            ]];
        }
        return ['errors' => [
            "error" => ["Ocurrió un error al procesar tu solicitud. Por favor, intenta nuevamente más tarde. Error: " . $e->getMessage()],
        ]];
    }
}
