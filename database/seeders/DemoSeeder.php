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
use Illuminate\Support\Facades\DB;
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

  
        DB::table('olympics')->insert([
            'id' => 2,
            'title' => 'Olimpiadas 2',
            'description' => 'COCHABAMBA',
            'price' => 120,
            'status'=>'activo',
            'start_date' => Carbon::create('2025', '06', '01'),
            'end_date' => Carbon::create('2025', '06', '01'),
        ]);

        
    }
}
