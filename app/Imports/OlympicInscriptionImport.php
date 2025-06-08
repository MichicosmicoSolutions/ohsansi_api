<?php

namespace App\Imports;

use App\Models\OlympicInscription;
use App\Models\Area;
use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

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

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $rows = $collection->skip(1); // saltar encabezado

        foreach ($rows as $row) {
            if (empty($row[0])) continue; // si no hay nombres, omitir

            // Buscar area_id y category_id por el texto mostrado
            $areaCategoryName = trim($row[15]); // columna P

            // Supongamos que el formato es "Área - Categoría"
            [$areaName, $categoryName] = explode(' - ', $areaCategoryName . ' - '); // fallback por si falta

            $area = \App\Models\Area::where('name', trim($areaName))->first();
            $category = \App\Models\Category::where('name', trim($categoryName))->first();

            OlympicInscription::create([
                // Estudiante
                'student_name' => $row[0],
                'student_lastname' => $row[1],
                'student_ci' => $row[2],
                'student_ci_place' => $row[3],
                'student_birthdate' => \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($row[4]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[4]) : $row[4],
                'student_email' => $row[5],
                'student_phone' => $row[6],
                'student_gender' => $row[7],

                // Tutor
                'tutor_name' => $row[8],
                'tutor_lastname' => $row[9],
                'tutor_ci' => $row[10],
                'tutor_ci_place' => $row[11],
                'tutor_email' => $row[12],
                'tutor_phone' => $row[13],
                'tutor_gender' => $row[14],

                // Profesor guía
                'teacher_name' => $row[16],
                'teacher_lastname' => $row[17],
                'teacher_ci' => $row[18],
                'teacher_ci_place' => $row[19],
                'teacher_email' => $row[20],
                'teacher_phone' => $row[21],
                'teacher_gender' => $row[22],

                // Relaciones y metadatos
                'school_id' => $this->schoolId,
                'olympiad_id' => $this->olympiadId,
                'accountable_relation_id' => $this->accountableRelationId,
                'area_id' => $area?->id,
                'category_id' => $category?->id,
            ]);
        }
    }
}
