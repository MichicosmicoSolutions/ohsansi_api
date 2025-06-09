<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Inscriptions;
use App\Models\Schools;
use App\Models\PersonalData;
use App\Models\Accountables;
use App\Models\LegalTutors;
use App\Models\Olympiads;
use App\Enums\InscriptionStatus;
use App\Models\BoletaDePago;
use Faker\Factory as Faker;

class InscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 6; $i++) {
            Inscriptions::create([
                'status' => $faker->randomElement(InscriptionStatus::getValues()),
                'drive_url' => $faker->optional()->url(),
                'school_id' => Schools::inRandomOrder()->value('id') ?? null,
                'competitor_data_id' => PersonalData::inRandomOrder()->value('id'),
                'accountable_id' => Accountables::inRandomOrder()->value('personal_data_id') ?? null,
                'legal_tutor_id' => LegalTutors::inRandomOrder()->value('personal_data_id') ?? null,
                'olympiad_id' => Olympiads::inRandomOrder()->value('id'),
                'boleta_de_pago_id' => BoletaDePago::inRandomOrder()->value('id'),
                'identifier' => $faker->randomNumber(7, true) . '|' . $faker->date('Y-m-d'),
            ]);
        }
    }
}
