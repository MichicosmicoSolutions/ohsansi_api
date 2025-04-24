<?php

namespace Database\Seeders;

use App\Models\Olympics;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
{
    $this->call([
        AreaAndCategoriesSeeder::class,
        PersonalDataSeeder::class, // 👈 nuevo seeder
    ]);

    User::factory(1)->create();
    Olympics::factory(1)->create();
}

}
