<?php

namespace App\Imports;

use App\Models\Inscriptions;
use App\Models\Olympiads;
use App\Models\Schools;
use App\Models\PersonalData;
use App\Models\LegalTutors;
use App\Models\Teachers;
use App\Models\SelectedAreas;
use App\Models\Accountables;
use App\Models\Area;
use App\Models\Category;
use App\Enums\InscriptionStatus;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use PhpOffice\PhpSpreadsheet\Shared\Date;

class OlympicInscriptionImport implements ToCollection
{
    private $schoolId;
    private $olympiadId;
    private $accountableRelationId;

    public function __construct($schoolId, $olympiadId, $accountableRelationId)
    {
        $this->schoolId = $schoolId;
        $this->olympiadId = $olympiadId;
        $this->accountableRelationId = $accountableRelationId;
    }

    public function collection(Collection $collection)
    {
        $rows = $collection->skip(1);

        foreach ($rows as $row) {
            $data = $this->mapRowToStructuredData($row);

            if (empty($data['student']['ci']) || empty($data['student']['birthdate'])) {
                continue;
            }

            // Crear estudiante
            $student = PersonalData::updateOrCreate(
                ['ci' => $data['student']['ci']],
                $data['student']
            );

            // Crear tutor legal
            $tutor = PersonalData::updateOrCreate(
                ['ci' => $data['tutor']['ci']],
                $data['tutor']
            );

            $legalTutorRelation = LegalTutors::firstOrCreate([
                'personal_data_id' => $tutor->id
            ]);

            // Inscripción
            $identifier = $student->ci . '|' . $student->birthdate;

            $inscription = Inscriptions::updateOrCreate(
                [
                    'identifier' => $identifier,
                    'olympiad_id' => $this->olympiadId,
                ],
                [
                    'status' => InscriptionStatus::DRAFT,
                    'legal_tutor_id' => $tutor->id,
                    'competitor_data_id' => $student->id,
                    'school_id' => $this->schoolId,
                    'accountable_id' => $this->accountableRelationId,
                ]
            );

            // Área y categoría
            $areaCategoryParts = explode(' - ', $data['area_category']);
            if (count($areaCategoryParts) !== 2) {
                continue; // Skip if the format is unexpected
            }

            [$areaName, $categoryName] = $areaCategoryParts;
            $area = Area::where('name', trim($areaName))->first();
            $category = Category::where('name', trim($categoryName))->first();

            if (!$area || !$category) continue;

            // Profesor guía
            $teacherId = null;
            if (!empty($data['teacher']['ci'])) {
                $teacher = PersonalData::updateOrCreate(
                    ['ci' => $data['teacher']['ci']],
                    $data['teacher']
                );

                $teacherRelation = Teachers::firstOrCreate([
                    'personal_data_id' => $teacher->id
                ]);

                $teacherId = $teacher->id;
            }

            SelectedAreas::updateOrCreate(
                [
                    'inscription_id' => $inscription->id,
                    'area_id' => $area->id,
                ],
                [
                    'category_id' => $category->id,
                    'teacher_id' => $teacherId,
                    'paid_at' => null,
                ]
            );

            $inscription->status = InscriptionStatus::PENDING;
            $inscription->save();
        }
    }

    private function mapRowToStructuredData($row): array
    {
        $studentBirthdate = is_numeric($row[4]) ? Date::excelToDateTimeObject($row[4])->format('Y-m-d') : $row[4];

        return [
            'student' => [
                'names' => $row[0],
                'last_names' => $row[1],
                'ci' => $row[2],
                'ci_expedition' => $row[3],
                'birthdate' => $studentBirthdate,
                'email' => $row[5],
                'phone_number' => $row[6],
                'gender' => $row[7],
            ],
            'tutor' => [
                'names' => $row[8],
                'last_names' => $row[9],
                'ci' => $row[10],
                'ci_expedition' => $row[11],
                'birthdate' => is_numeric($row[11]) ? Date::excelToDateTimeObject($row[11])->format('Y-m-d') : ($row[11] ?: null),
                'email' => $row[12],
                'phone_number' => $row[13],
                'gender' => $row[14],
            ],
            'area_category' => $row[15],
            'teacher' => [
                'names' => $row[16],
                'last_names' => $row[17],
                'ci' => $row[18],
                'ci_expedition' => $row[19],
                'birthdate' => now()->format('Y-m-d'),
                'email' => $row[20],
                'phone_number' => $row[21],
                'gender' => $row[22],
            ],
        ];
    }
}
