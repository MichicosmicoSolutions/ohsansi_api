<?php

namespace Database\Factories;

use App\Models\Olympiads;
use Illuminate\Database\Eloquent\Factories\Factory;

class OlympiadsFactory extends Factory
{
    protected $model = Olympiads::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'status' => 'Publico',
            'description' => $this->faker->paragraph,
            'price' => 1500,
            'presentation' => $this->faker->paragraph,
            'requirements' => $this->faker->paragraph,
            'awards' => $this->faker->paragraph,
            'start_date' => '2025-04-01',
            'end_date' => '2025-10-01',
            'contacts' => $this->faker->phoneNumber,
        ];
    }
}
