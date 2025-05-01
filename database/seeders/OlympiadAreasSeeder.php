<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OlympiadAreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $olympiad = DB::table("olympiads")->first();

        $areas = DB::table("areas")->get();

        foreach ($areas as $area) {
            $categories = DB::table("categories")->get();
            foreach ($categories as $category) {
                DB::table('olympiad_areas')->insert([
                    'olympiad_id' => $olympiad->id,
                    'area_id' => $area->id,
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
