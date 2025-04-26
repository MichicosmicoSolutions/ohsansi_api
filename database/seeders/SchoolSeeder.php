<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('schools')->insert([
            'id' => 1,
            'name' => 'BUENAS NUEVAS',
            'department' => 'COCHABAMBA',
            'province' => 'CERCADO',
            'created_at' => Carbon::create('2025', '06', '01'),
            'updated_at' => Carbon::create('2025', '06', '01'),
        ]);
    }
}
