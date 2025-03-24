<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $areas = [
            ['name' => 'ASTRONOMÍA - ASTROFÍSICA', 'description' => 'Exploración del universo y los astros.', 'monto_precio' => '500.00'],
            ['name' => 'BIOLOGÍA', 'description' => 'Estudio de los seres vivos y su entorno.', 'monto_precio' => '450.00'],
            ['name' => 'FÍSICA', 'description' => 'Ciencia que estudia la materia y la energía.', 'monto_precio' => '600.00'],
            ['name' => 'INFORMÁTICA', 'description' => 'Desarrollo de software y análisis de datos.', 'monto_precio' => '700.00'],
            ['name' => 'MATEMÁTICAS', 'description' => 'Cálculo, álgebra y estructuras numéricas.', 'monto_precio' => '550.00'],
            ['name' => 'QUÍMICA', 'description' => 'Estudio de la composición de las sustancias.', 'monto_precio' => '480.00'],
            ['name' => 'ROBÓTICA', 'description' => 'Diseño y programación de sistemas automatizados.', 'monto_precio' => '750.00']
        ];
        DB::table('areas')->insert($areas);
    }
}
