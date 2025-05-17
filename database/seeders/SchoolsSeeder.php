<?php

namespace Database\Seeders;

use App\Enums\Department;
use App\Models\Schools;
use Illuminate\Database\Seeder;

class SchoolsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $schools = [
            ['name' => 'Colegio San Ignacio', 'department' => Department::SANTA_CRUZ, 'province' => 'Andrés Ibáñez'],
            ['name' => 'Colegio Alemán Mariscal Braun', 'department' => Department::LA_Paz, 'province' => 'Murillo'],
            ['name' => 'Colegio La Salle', 'department' => Department::CBBA, 'province' => 'Cercado'],
            ['name' => 'Colegio Don Bosco', 'department' => Department::ORURO, 'province' => 'Cercado'],
            ['name' => 'Colegio San Martín', 'department' => Department::POTOSI, 'province' => 'Tomás Frías'],
            ['name' => 'Colegio Boliviano Japonés', 'department' => Department::BENI, 'province' => 'Cercado'],
            ['name' => 'Colegio Emaús', 'department' => Department::TARIJA, 'province' => 'Cercado'],
            ['name' => 'Colegio Santa Ana', 'department' => Department::PANDO, 'province' => 'Manuripi'],
            ['name' => 'Colegio La Recoleta', 'department' => Department::CBBA, 'province' => 'Cercado'],
            ['name' => 'Colegio Santa Teresa', 'department' => Department::LA_Paz, 'province' => 'Murillo'],
        ];

        foreach ($schools as $school) {
            Schools::create($school);
        }
    }
}
