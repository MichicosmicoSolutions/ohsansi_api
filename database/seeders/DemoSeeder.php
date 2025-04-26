<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonalData;
use App\Models\LegalTutors;
use App\Models\Competitors;
use App\Models\Inscriptions;
use App\Models\Areas;
use App\Models\Categories;
use App\Models\Olympics;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run()
    {
        // Creamos algunos personal_data
        $personalDatas = collect();
        for ($i = 1; $i <= 10; $i++) {
            $personalDatas->push(PersonalData::create([
                'ci' => 1000000 + $i,
                'ci_expedition' => 'LP',
                'names' => 'Nombre' . $i,
                'last_names' => 'Apellido' . $i,
                'birthdate' => '2000-01-01',
                'email' => 'persona' . $i . '@test.com',
                'phone_number' => '7000000' . $i,
            ]));
        }

        // Creamos legal_tutors para cada personal_data
        $legalTutors = collect();
        foreach ($personalDatas as $personalData) {
            $legalTutors->push(LegalTutors::create([
                'personal_data_id' => $personalData->id,
            ]));
        }

        // Aseguramos que existan al menos unas areas, categories y olympics
        $area = Areas::firstOrCreate(['name' => 'Natación'],
        ['description' => 'Área de prueba para natación']);
        $category = Categories::firstOrCreate(
            ['name' => 'Infantil'],
            [
                'range_course' => 'BEGINNER', // Asegúrate que es válido según tu enum
                'area_id' => $area->id,
            ]
        );
        $olympic = Olympics::firstOrCreate(
            ['title' => 'Olimpiadas 2025'],
            [
                'description' => 'Competencia nacional de prueba',
                'price' => 150,
                'start_date' => Carbon::parse('2025-06-01'),
                'end_date' => Carbon::parse('2025-06-07'),
            ]
        );

        // Creamos competitors
        $competitors = collect();
        foreach ($personalDatas as $index => $personalData) {
            $competitors->push(Competitors::create([
                'course' => 'BEGINNER', // O como esté definido tu enum
                'school_id' => 1, // Asegúrate que school_id 1 exista o cambia aquí
                'legal_tutor_id' => $legalTutors[$index]->id,
                'personal_data_id' => $personalData->id,
            ]));
        }

        // Creamos inscriptions
        foreach ($competitors as $competitor) {
            Inscriptions::create([
                'competitor_id' => $competitor->id,
                'drive_url' => null,
                'olympic_id' => $olympic->id,
                'area_id' => $area->id,
                'category_id' => $category->id,
                'status' => 'Active', // o algún valor permitido por tu Enum
                'paid_at' => Carbon::now(),
            ]);
        }
    }
}
