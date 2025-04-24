<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PersonalDataSeeder extends Seeder
{
    public function run()
    {
        DB::table('personal_data')->insert([
            [
                'ci' => 12345678,
                'ci_expedition' => 'LP',
                'names' => 'Juan',
                'last_names' => 'Pérez Gómez',
                'birthdate' => Carbon::parse('2005-06-15'),
                'email' => 'juan.perez@example.com',
                'phone_number' => '71234567',
            ],
            [
                'ci' => 23456789,
                'ci_expedition' => 'CB',
                'names' => 'María',
                'last_names' => 'López Fernández',
                'birthdate' => Carbon::parse('2006-09-22'),
                'email' => 'maria.lopez@example.com',
                'phone_number' => '72345678',
            ],
            [
                'ci' => 34567890,
                'ci_expedition' => 'SC',
                'names' => 'Carlos',
                'last_names' => 'Gutiérrez Ruiz',
                'birthdate' => Carbon::parse('2007-03-10'),
                'email' => 'carlos.gutierrez@example.com',
                'phone_number' => '73456789',
            ],
        ]);
    }
}
