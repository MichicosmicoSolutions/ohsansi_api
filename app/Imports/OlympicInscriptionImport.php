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
use App\Models\Areas;
use App\Models\BoletaDePago;
use App\Models\Categories;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class OlympicInscriptionImport implements WithMultipleSheets
{
    public $schoolId;
    public $olympiad;
    public $accountableRelationId;
    public $accountableData;
    public $areaMap = [];
    public $file;

    public function __construct($schoolId, $olympiad, $accountableRelationId, $accountableData, $file)
    {
        $this->schoolId = $schoolId;
        $this->olympiad = $olympiad;
        $this->accountableRelationId = $accountableRelationId;
        $this->accountableData = $accountableData;
        $this->file = $file; // Agregado para manejar la ubicación del archivo Excel
    }

    public function sheets(): array
    {
        $sheets = [];
        $spreadsheet = IOFactory::load($this->file);
        $sheetCount = $spreadsheet->getSheetCount();

        // Verifica si la hoja 'Inscripciones' existe en el archivo Excel
        if ($sheetCount > 0) {
            $sheets['Inscripciones'] = new class($this) implements ToCollection {
                private $importer;

                public function __construct($importer)
                {
                    $this->importer = $importer;
                }

                public function collection(Collection $collection)
                {
                    $rows = $collection->skip(1);

                    foreach ($rows as $index => $row) {
                        try {
                            $data = $this->mapRowToStructuredData($row);
                        } catch (\Exception $e) {
                            Log::error('Error processing row #' . ($index + 1));
                            continue;
                        }


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
                                'olympiad_id' => $this->importer->olympiad->id,
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
                        $parts = explode(' / ', $areaKey);
                        if (count($parts) < 2) {
                            throw new \Exception('Invalid area_category content format in row: ' . implode(',', $index + 1));
                        }

                        $areaName = mb_strtoupper($parts[0], 'UTF-8');
                        $categoryName = mb_strtoupper($parts[1], 'UTF-8');

                        $area = Areas::where('name', $areaName)->first();
                        $category = Categories::where('name', $parts[1])->first();

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

                        $selectedAreas = SelectedAreas::updateOrCreate(
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

                        $randomNumber = rand(100000, 999999);
                        $count = count($inscription->selected_areas);
                        $boleta = BoletaDePago::firstOrCreate([
                            'id' => $inscription->id,
                        ], [
                            'numero_orden_de_pago' => $randomNumber,
                            'ci' => $this->importer->accountableData->ci,
                            'status' => 'pending',
                            'nombre' => $this->importer->accountableData->names,
                            'apellido' => $this->importer->accountableData->last_names,
                            'fecha_nacimiento' => $this->importer->accountableData->birthdate,
                            'cantidad' => 1,
                            'concepto' => 'Inscripción Olimpiada: ' . $this->importer->olympiad->name,
                            'precio_unitario' => $this->importer->olympiad->price,
                            'importe' => $this->importer->olympiad->price,
                            'total' => $this->importer->olympiad->price,
                        ]);

                        $inscription->status = InscriptionStatus::PENDING;
                        $inscription->boleta_de_pago_id = $boleta->id;
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
                            'birthdate' => is_numeric($row[4])
                                ? Date::excelToDateTimeObject($row[4])->format('Y-m-d')
                                : Carbon::createFromFormat('d/m/Y', $row[4])->format('Y-m-d'),
                            'email' => $row[5],
                            'phone_number' => $row[6],
                            'gender' => $row[7],
                        ],
                        'tutor' => [
                            'names' => $row[8],
                            'last_names' => $row[9],
                            'ci' => $row[10],
                            'ci_expedition' => $row[11],
                            'birthdate' => is_numeric($row[12])
                                ? Date::excelToDateTimeObject($row[12])->format('Y-m-d')
                                : Carbon::createFromFormat('d/m/Y', $row[12])->format('Y-m-d'),
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
                            'birthdate' => is_numeric($row[21])
                                ? Date::excelToDateTimeObject($row[21])->format('Y-m-d')
                                : Carbon::createFromFormat('d/m/Y', $row[21])->format('Y-m-d'),
                            'email' => $row[22],
                            'phone_number' => $row[23],
                            'gender' => $row[24],
                        ],
                    ];
                }
            };
        } else {
            Log::error('La hoja "Inscripciones" no fue encontrada en el archivo Excel.');
        }

        // Verifica si la hoja 'Areas' existe en el archivo Excel
        // if ($sheetCount > 1) {
        //     $sheets['Areas'] = new class($this) implements ToCollection {
        //         private $importer;

        //         public function __construct($importer)
        //         {
        //             $this->importer = $importer;
        //         }

        //         public function collection(Collection $rows)
        //         {
        //             foreach ($rows->skip(1) as $row) {
        //                 $name = trim($row[0]); // nombre
        //                 $areaId = $row[1];     // area_id
        //                 $categoryId = $row[2]; // category_id

        //                 $this->importer->areaMap[$name] = [
        //                     'area_id' => $areaId,
        //                     'category_id' => $categoryId,
        //                 ];
        //             }
        //         }
        //     };
        // } else {
        //     Log::error('La hoja "Areas" no fue encontrada en el archivo Excel.');
        // }

        return $sheets;
    }
}
