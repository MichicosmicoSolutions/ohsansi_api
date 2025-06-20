<?php

namespace Database\Seeders;

use App\Enums\Department;
use App\Models\PersonalData;
use App\Models\Accountables;
use App\Models\LegalTutors;
use App\Models\Teachers;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PersonalDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Generar 10 registros para PersonalData
        for ($i = 0; $i < 10; $i++) {
            $personalData = PersonalData::create([
                'ci' => $faker->unique()->numberBetween(1000000, 9999999),
                'ci_expedition' => $faker->randomElement(Department::getValues()),
                'names' => $faker->firstName . ' ' . $faker->firstName,
                'last_names' => $faker->lastName . ' ' . $faker->lastName,
                'birthdate' => $faker->dateTimeBetween('-30 years', '-15 years')->format('Y-m-d'),
                'email' => $faker->unique()->safeEmail,
                'phone_number' => $faker->phoneNumber,
                'gender' => $faker->randomElement(['M', 'F'])
            ]);

            // Asignar roles aleatorios para probar relaciones
            $role = $faker->randomElement(['accountable', 'legal_tutor', 'teacher']);

            switch ($role) {
                case 'accountable':
                    Accountables::create(['personal_data_id' => $personalData->id]);
                    break;
                case 'legal_tutor':
                    LegalTutors::create(['personal_data_id' => $personalData->id]);
                    break;
                case 'teacher':
                    Teachers::create(['personal_data_id' => $personalData->id]);
                    break;
            }
        }
    }
}
