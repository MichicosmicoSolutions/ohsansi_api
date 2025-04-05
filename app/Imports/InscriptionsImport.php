<?php

namespace App\Imports;

use App\Models\Areas;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InscriptionsImport implements ToCollection
{
    private $data;

    public function collection(Collection $collection)
    {
        $this->data = [];

        foreach ($collection->slice(1) as $row) {
            if ($row->count() < 10) continue;

            $allNull = $row->every(function ($value) {
                return is_null($value);
            });
            if ($allNull) continue;
            $areas = array_map('trim', explode(',', $row[11]));
            $areaIds = Areas::whereIn('name', $areas)->pluck('id')->toArray();
            if (empty($areaIds)) {
                $areaIds = [0, 0];
            }

            $birthdate = Date::excelToDateTimeObject($row[4])->format('Y-m-d');
            $this->data[] = [
                'ci' => $row[0],
                'ci_expedition' => $row[1],
                'names' => $row[2],
                'last_names' => $row[3],
                'birthdate' => $birthdate,
                'email' => $row[5],
                'phone_number' => (string)$row[6],
                'school_data' => [
                    'name' => $row[7],
                    'department' => $row[8],
                    'province' => $row[9],
                    'course' => $row[10],
                ],
                'selected_areas' => $areaIds,
            ];
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
