<?php

namespace Database\Factories;

use App\Enums\Publish;
use App\Models\Olympiads;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class OlympiadsFactory extends Factory
{
    protected $model = Olympiads::class;

    public function definition()
    {
        return [
            'title' => 'Olimpiada general 2025',
            'status' => 'published',
            'description' => 'Descripción de la olimpiada de general 2025',
            'price' => 15,
            "publish" => Publish::Inscripcion,
            'presentation' => $this->faker->paragraph,
            'requirements' => $this->faker->paragraph,
            'awards' => $this->faker->paragraph,
            'start_date' => Carbon::parse('2024-09-01'),
            'end_date' => Carbon::parse('2024-11-30'),
            'contacts' => "Email: {$this->faker->email}, Phone: {$this->faker->phoneNumber}",
        ];
    }
}
