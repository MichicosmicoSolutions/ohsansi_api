<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoletaDePagoSeeder extends Seeder
{
    public function run()
    {
        DB::table('boleta_de_pago')->insert([
            [
                'numero_orden_de_pago' => '0000282499',
                'ci' => '8846245',
                'status' => 'pending',
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'fecha_nacimiento' => '1990-01-01',
                'cantidad' => 1,
                'concepto' => 'Inscripción Olimpiada',
                'precio_unitario' => 100.00,
                'importe' => 100.00,
                'total' => 100.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'numero_orden_de_pago' => '0000282500',
                'ci' => '8846246',
                'status' => 'pending',
                'nombre' => 'María',
                'apellido' => 'Gómez',
                'fecha_nacimiento' => '1995-03-15',
                'cantidad' => 2,
                'concepto' => 'Inscripción Olimpiada',
                'precio_unitario' => 90.00,
                'importe' => 180.00,
                'total' => 180.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'numero_orden_de_pago' => '0000282501',
                'ci' => '8846247',
                'status' => 'pending',
                'nombre' => 'Carlos',
                'apellido' => 'Ramírez',
                'fecha_nacimiento' => '1988-07-23',
                'cantidad' => 1,
                'concepto' => 'Pago Extra',
                'precio_unitario' => 50.00,
                'importe' => 50.00,
                'total' => 50.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'numero_orden_de_pago' => '0000282502',
                'ci' => '8846248',
                'status' => 'pending',
                'nombre' => 'Ana',
                'apellido' => 'Martínez',
                'fecha_nacimiento' => '2000-11-10',
                'cantidad' => 3,
                'concepto' => 'Inscripción Olimpiada',
                'precio_unitario' => 70.00,
                'importe' => 210.00,
                'total' => 210.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'numero_orden_de_pago' => '0000282503',
                'ci' => '8846249',
                'status' => 'pending',
                'nombre' => 'Luis',
                'apellido' => 'Fernández',
                'fecha_nacimiento' => '1992-05-05',
                'cantidad' => 1,
                'concepto' => 'Pago Extra',
                'precio_unitario' => 60.00,
                'importe' => 60.00,
                'total' => 60.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'numero_orden_de_pago' => '0000282504',
                'ci' => '8846250',
                'status' => 'pending',
                'nombre' => 'Sofía',
                'apellido' => 'Lopez',
                'fecha_nacimiento' => '1998-09-30',
                'cantidad' => 2,
                'concepto' => 'Inscripción Olimpiada',
                'precio_unitario' => 85.00,
                'importe' => 170.00,
                'total' => 170.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
