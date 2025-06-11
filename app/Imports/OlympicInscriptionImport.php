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
use App\Enums\InscriptionStatus;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ToCollection;

use PhpOffice\PhpSpreadsheet\Shared\Date;

class OlympicInscriptionImport implements WithMultipleSheets
{
    protected $schoolId;
    protected $olympiadId;
    protected $accountableRelationId;
    protected $areaMap = [];

    public function __construct($schoolId, $olympiadId, $accountableRelationId)
    {
        $this->schoolId = $schoolId;
        $this->olympiadId = $olympiadId;
        $this->accountableRelationId = $accountableRelationId;
    }

    public function sheets(): array
    {
        return [
            'Inscripciones' => new class($this) implements ToCollection {
                private $importer;

                public function __construct($importer)
                {
                    $this->importer = $importer;
                }

                public function collection(Collection $collection)
                {
                    $rows = $collection->skip(1);

                    foreach ($rows as $row) {
                        $data = $this->mapRowToStructuredData($row);

                        if (empty($data['student']['ci']) || empty($data['student']['birthdate'])) {
                            continue;
                        }

                        $student = PersonalData::updateOrCreate(
                            ['ci' => $data['student']['ci']],
                            $data['student']
                        );

                        $tutor = PersonalData::updateOrCreate(
                            ['ci' => $data['tutor']['ci']],
                            $data['tutor']
                        );

                        $legalTutorRelation = LegalTutors::firstOrCreate([
                            'personal_data_id' => $tutor->id
                        ]);

                        $identifier = $student->ci . '|' . $student->birthdate;

                        $inscription = Inscriptions::updateOrCreate(
                            [
                                'identifier' => $identifier,
                                'olympiad_id' => $this->importer->olympiadId,
                            ],
                            [
                                'status' => InscriptionStatus::DRAFT,
                                'legal_tutor_id' => $tutor->id,
                                'competitor_data_id' => $student->id,
                                'school_id' => $this->importer->schoolId,
                                'accountable_id' => $this->importer->accountableRelationId,
                            ]
                        );

                        $areaKey = trim($data['area_category']);
                        if (!isset($this->importer->areaMap[$areaKey])) continue;

                        $areaId = $this->importer->areaMap[$areaKey]['area_id'];
                        $categoryId = $this->importer->areaMap[$areaKey]['category_id'];

                        $teacherId = null;
                        if (!empty($data['teacher']['ci'])) {
                            $teacher = PersonalData::updateOrCreate(
                                ['ci' => $data['teacher']['ci']],
                                $data['teacher']
                            );

                            Teachers::firstOrCreate([
                                'personal_data_id' => $teacher->id
                            ]);

                            $teacherId = $teacher->id;
                        }

                        SelectedAreas::updateOrCreate(
                            [
                                'inscription_id' => $inscription->id,
                                'area_id' => $areaId,
                            ],
                            [
                                'category_id' => $categoryId,
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
                    return [
                        'student' => [
                            'names' => $row[0],
                            'last_names' => $row[1],
                            'ci' => $row[2],
                            'ci_expedition' => $row[3],
                            'birthdate' => is_numeric($row[4]) ? Date::excelToDateTimeObject($row[4])->format('Y-m-d') : $row[4],
                            'email' => $row[5],
                            'phone_number' => $row[6],
                            'gender' => $row[7],
                        ],
                        'tutor' => [
                            'names' => $row[8],
                            'last_names' => $row[9],
                            'ci' => $row[10],
                            'ci_expedition' => $row[11],
                            'birthdate' => is_numeric($row[12]) ? Date::excelToDateTimeObject($row[12])->format('Y-m-d') : $row[12],
                            'email' => $row[13],
                            'phone_number' => $row[14],
                            'gender' => $row[15],
                        ],
                        'area_category' => $row[16],
                        'teacher' => [
                            'names' => $row[17],
                            'last_names' => $row[18],
                            'ci' => $row[19],
                            'ci_expedition' => $row[20],
                            'birthdate' => now()->format('Y-m-d'),
                            'email' => $row[21],
                            'phone_number' => $row[22],
                            'gender' => $row[23],
                        ],
                    ];
                }
            },

            'Areas' => new class($this) implements ToCollection {
                private $importer;

                public function __construct($importer)
                {
                    $this->importer = $importer;
                }

                public function collection(Collection $rows)
                {
                    foreach ($rows->skip(1) as $row) {
                        $name = trim($row[0]); // nombre
                        $areaId = $row[1];     // area_id
                        $categoryId = $row[2]; // category_id

                        $this->importer->areaMap[$name] = [
                            'area_id' => $areaId,
                            'category_id' => $categoryId,
                        ];
                    }
                }
            },
        ];
    }
}
