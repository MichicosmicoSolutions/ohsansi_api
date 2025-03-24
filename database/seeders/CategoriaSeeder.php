<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      $categorias = [
            ['name' => '3P', 'range_course' => '3ro Primaria', 'area_id' => 1],
            ['name' => '4P', 'range_course' => '4to Primaria', 'area_id' => 1],
            ['name' => '5P', 'range_course' => '5to Primaria', 'area_id' => 2],
            ['name' => '6P', 'range_course' => '6to Primaria', 'area_id' => 2],
            ['name' => '1S', 'range_course' => '1ro Secundaria', 'area_id' => 3],
            ['name' => '2S', 'range_course' => '2do Secundaria', 'area_id' => 3],
            ['name' => '3S', 'range_course' => '3ro Secundaria', 'area_id' => 4],
            ['name' => '4S', 'range_course' => '4to Secundaria', 'area_id' => 4],
            ['name' => '5S', 'range_course' => '5to Secundaria', 'area_id' => 5],
            ['name' => '6S', 'range_course' => '6to Secundaria', 'area_id' => 5],
            ['name' => 'Guacamayo', 'range_course' => '5to a 6to Primaria', 'area_id' => 6],
            ['name' => 'Guanaco', 'range_course' => '1ro a 3ro Secundaria', 'area_id' => 6],
            ['name' => 'Londra', 'range_course' => '1ro a 3ro Secundaria', 'area_id' => 6],
            ['name' => 'Jucumari', 'range_course' => '4to a 6to Secundaria', 'area_id' => 6],
            ['name' => 'Bufeo', 'range_course' => '1ro a 3ro Secundaria', 'area_id' => 3],
            ['name' => 'Puma', 'range_course' => '4to a 6to Secundaria', 'area_id' => 3],
            ['name' => 'Primer Nivel', 'range_course' => '1ro Secundaria', 'area_id' => 7],
            ['name' => 'Segundo Nivel', 'range_course' => '2do Secundaria', 'area_id' => 7],
            ['name' => 'Tercer Nivel', 'range_course' => '3ro Secundaria', 'area_id' => 7],
            ['name' => 'Cuarto Nivel', 'range_course' => '4to Secundaria', 'area_id' => 7],
        ];

        DB::table('categorias')->insert($categorias);
    }
}
