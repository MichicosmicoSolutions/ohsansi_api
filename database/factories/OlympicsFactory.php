<?php

namespace Database\Factories;

use App\Models\Olympics;
use Illuminate\Database\Eloquent\Factories\Factory;

class OlympicsFactory extends Factory
{
    protected $model = Olympics::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'start_date' => '2025-04-01',
            'end_date' => '2025-10-01',
        ];
    }
}
