<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AreaAndCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $areas = [
            ['name' => 'ASTRONOMÍA - ASTROFÍSICA', 'description' => 'Exploración del universo y los astros.', 'price' => 5000],
            ['name' => 'BIOLOGÍA', 'description' => 'Estudio de los seres vivos y su entorno.', 'price' => 4500],
            ['name' => 'FÍSICA', 'description' => 'Ciencia que estudia la materia y la energía.', 'price' => 6000],
            ['name' => 'INFORMÁTICA', 'description' => 'Desarrollo de software y análisis de datos.', 'price' => 7000],
            ['name' => 'MATEMÁTICAS', 'description' => 'Cálculo, álgebra y estructuras numéricas.', 'price' => 5500],
            ['name' => 'QUÍMICA', 'description' => 'Estudio de la composición de las sustancias.', 'price' => 4800],
            ['name' => 'ROBÓTICA', 'description' => 'Diseño y programación de sistemas automatizados.', 'price' => 7500]
        ];

        DB::table('areas')->insert($areas);

        $areasInserted = DB::table('areas')->where('name', 'ASTRONOMÍA - ASTROFÍSICA')->first();
        $areaIdAstronomia = $areasInserted->id;

        $areasInserted = DB::table('areas')->where('name', 'BIOLOGÍA')->first();
        $areaIdBiologia = $areasInserted->id;

        $areasInserted = DB::table('areas')->where('name', 'FÍSICA')->first();
        $areaIdFisica = $areasInserted->id;

        $areasInserted = DB::table('areas')->where('name', 'INFORMÁTICA')->first();
        $areaIdInformatica = $areasInserted->id;

        $areasInserted = DB::table('areas')->where('name', 'MATEMÁTICAS')->first();
        $areaIdMatematicas = $areasInserted->id;

        $areasInserted = DB::table('areas')->where('name', 'QUÍMICA')->first();
        $areaIdQuimica = $areasInserted->id;

        $areasInserted = DB::table('areas')->where('name', 'ROBÓTICA')->first();
        $areaIdRobotica = $areasInserted->id;

        $categories = [
            ['name' => '3P', 'range_course' => json_encode(['3ro Primaria']), 'area_id' => $areaIdAstronomia],
            ['name' => '4P', 'range_course' => json_encode(['4to Primaria']), 'area_id' => $areaIdAstronomia],
            ['name' => '5P', 'range_course' => json_encode(['5to Primaria']), 'area_id' => $areaIdBiologia],
            ['name' => '6P', 'range_course' => json_encode(['6to Primaria']), 'area_id' => $areaIdBiologia],
            ['name' => '1S', 'range_course' => json_encode(['1ro Secundaria']), 'area_id' => $areaIdFisica],
            ['name' => '2S', 'range_course' => json_encode(['2do Secundaria']), 'area_id' => $areaIdFisica],
            ['name' => '3S', 'range_course' => json_encode(['3ro Secundaria']), 'area_id' => $areaIdInformatica],
            ['name' => '4S', 'range_course' => json_encode(['4to Secundaria']), 'area_id' => $areaIdInformatica],
            ['name' => '5S', 'range_course' => json_encode(['5to Secundaria']), 'area_id' => $areaIdMatematicas],
            ['name' => '6S', 'range_course' => json_encode(['6to Secundaria']), 'area_id' => $areaIdMatematicas],
            ['name' => 'Guacamayo', 'range_course' => json_encode(['5to Primaria', '6to Primaria']), 'area_id' => $areaIdQuimica],
            ['name' => 'Guanaco', 'range_course' => json_encode(['1ro Secundaria', '2do Secundaria', '3ro Secundaria']), 'area_id' => $areaIdQuimica],
            ['name' => 'Londra', 'range_course' => json_encode(['1ro Secundaria', '2do Secundaria', '3ro Secundaria']), 'area_id' => $areaIdQuimica],
            ['name' => 'Jucumari', 'range_course' => json_encode(['4to Secundaria', '5to Secundaria', '6to Secundaria']), 'area_id' => $areaIdQuimica],
            ['name' => 'Bufeo', 'range_course' => json_encode(['1ro Secundaria', '2do Secundaria', '3ro Secundaria']), 'area_id' => $areaIdFisica],
            ['name' => 'Puma', 'range_course' => json_encode(['4to Secundaria', '5to Secundaria', '6to Secundaria']), 'area_id' => $areaIdFisica],
            ['name' => 'Primer Nivel', 'range_course' => json_encode(['1ro Secundaria']), 'area_id' => $areaIdRobotica],
            ['name' => 'Segundo Nivel', 'range_course' => json_encode(['2do Secundaria']), 'area_id' => $areaIdRobotica],
            ['name' => 'Tercer Nivel', 'range_course' => json_encode(['3ro Secundaria']), 'area_id' => $areaIdRobotica],
            ['name' => 'Cuarto Nivel', 'range_course' => json_encode(['4to Secundaria']), 'area_id' => $areaIdRobotica],
        ];

        DB::table('categories')->insert($categories);
    }
}
