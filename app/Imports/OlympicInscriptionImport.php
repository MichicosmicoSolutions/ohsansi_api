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
            if (empty($row[0]) || empty($row[2]) || empty($row[4])) {
                continue; // Saltar si faltan datos clave
            }

            // ---------------------------
            // 1. Crear o buscar estudiante
            // ---------------------------
            $birthdate = is_numeric($row[4])
                ? Date::excelToDateTimeObject($row[4])->format('Y-m-d')
                : $row[4];

            $studentData = [
                'ci' => $row[2],
                'ci_expedition' => $row[3],
                'birthdate' => $birthdate,
                'names' => $row[0],
                'last_names' => $row[1],
                'email' => $row[5],
                'phone_number' => $row[6],
                'gender' => $row[7],
            ];

            $student = PersonalData::updateOrCreate(
                ['ci' => $studentData['ci']],
                $studentData
            );

            // ----------------------------
            // 2. Crear o buscar tutor legal
            // ----------------------------
            $tutorData = [
                'ci' => $row[10],
                'ci_expedition' => $row[11],
                'birthdate' => now(), // Si no hay en el Excel, puedes asignar temporal o por lógica propia
                'names' => $row[8],
                'last_names' => $row[9],
                'email' => $row[12],
                'phone_number' => $row[13],
                'gender' => $row[14],
            ];

            $tutor = PersonalData::updateOrCreate(
                ['ci' => $tutorData['ci']],
                $tutorData
            );

            $legalTutorRelation = LegalTutors::firstOrCreate([
                'personal_data_id' => $tutor->id
            ]);

            // --------------------------------
            // 3. Generar la inscripción (draft)
            // --------------------------------
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

            // -------------------------------------
            // 4. Obtener área y categoría seleccionada
            // -------------------------------------
            $areaCategoryText = $row[15]; // "Área - Categoría"
            [$areaName, $categoryName] = explode(' - ', $areaCategoryText . ' - ');
            $area = Area::where('name', trim($areaName))->first();
            $category = Category::where('name', trim($categoryName))->first();

            if (!$area || !$category) {
                continue; // Saltar si no se encuentran
            }

            // ------------------------
            // 5. Crear profesor guía
            // ------------------------
            $teacherData = [
                'ci' => $row[18],
                'ci_expedition' => $row[19],
                'birthdate' => now(),
                'names' => $row[16],
                'last_names' => $row[17],
                'email' => $row[20],
                'phone_number' => $row[21],
                'gender' => $row[22],
            ];

            $teacherId = null;

            if (!empty($teacherData['ci'])) {
                $teacher = PersonalData::updateOrCreate(
                    ['ci' => $teacherData['ci']],
                    $teacherData
                );

                $teacherRelation = Teachers::firstOrCreate([
                    'personal_data_id' => $teacher->id,
                ]);

                $teacherId = $teacher->id;
            }

            // ------------------------
            // 6. Registrar área elegida
            // ------------------------
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

            // ----------------------------
            // 7. Marcar inscripción completa
            // ----------------------------
            $inscription->status = InscriptionStatus::PENDING;
            $inscription->save();
        }
    }
}
