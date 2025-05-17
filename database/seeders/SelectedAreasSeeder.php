<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SelectedAreas;
use App\Models\Inscriptions;
use App\Models\Areas;
use App\Models\Categories;
use App\Models\Teachers;
use Carbon\Carbon;

class SelectedAreasSeeder extends Seeder
{
    public function run()
    {
        $inscriptions = Inscriptions::all();
        $areas = Areas::all();
        $categories = Categories::all();
        $teachers = Teachers::all();

        // Generar 6 registros de ejemplo
        for ($i = 1; $i <= 6; $i++) {
            SelectedAreas::create([
                'paid_at' => rand(0, 1) ? Carbon::now()->subDays(rand(1, 30)) : null,
                'inscription_id' => $inscriptions->random()->id,
                'area_id' => $areas->random()->id,
                'category_id' => $categories->random()->id,
                'teacher_id' => $teachers->random()->personal_data_id,
            ]);
        }
    }
}
