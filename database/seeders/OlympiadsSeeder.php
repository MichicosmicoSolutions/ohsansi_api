<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Olympiads;
use App\Enums\Publish;
use Carbon\Carbon;

class OlympiadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $olympiads = [
            [
                'title' => 'International Mathematics Olympiad 2024',
                'status' => 'published',
                'publish' => Publish::Inscripcion,
                'description' => 'A global competition for students passionate about mathematics.',
                'price' => 50,
                'presentation' => 'Includes challenging problem sets and final presentations.',
                'requirements' => 'Open to students under 20 years old.',
                'awards' => 'Gold, Silver, Bronze medals. Scholarships for the top 3.',
                'start_date' => Carbon::parse('2024-09-01'),
                'end_date' => Carbon::parse('2024-11-30'),
                'contacts' => 'Email: contact@imo2024.org, Phone: +1-555-123-4567',
            ],
            [
                'title' => 'National Physics Olympiad 2025',
                'status' => 'published',
                'publish' => Publish::Inscripcion,
                'description' => 'A national competition for high school students interested in physics.',
                'price' => 40,
                'presentation' => 'Virtual labs and practical problem solving.',
                'requirements' => 'Open to high school students with a passion for physics.',
                'awards' => 'Internships, scholarships, and national recognition.',
                'start_date' => Carbon::parse('2025-01-15'),
                'end_date' => Carbon::parse('2025-03-15'),
                'contacts' => 'Email: physics@natolympiad.org, Phone: +1-555-987-6543',
            ],
            [
                'title' => 'Chemistry Challenge 2025',
                'status' => 'draft',
                'publish' => Publish::Borrador,
                'description' => 'Advanced chemistry competition for students worldwide.',
                'price' => 60,
                'presentation' => 'Includes practical labs and theoretical exams.',
                'requirements' => 'Open to high school students with strong chemistry skills.',
                'awards' => 'Scholarships and research grants.',
                'start_date' => Carbon::parse('2025-02-01'),
                'end_date' => Carbon::parse('2025-04-01'),
                'contacts' => 'Email: info@chemchallenge.org, Phone: +1-555-456-7890',
            ],
            [
                'title' => 'Robotics Challenge 2025',
                'status' => 'draft',
                'publish' => Publish::Borrador,
                'description' => 'A global robotics competition for students passionate about technology.',
                'price' => 90,
                'presentation' => 'Includes robot building and coding challenges.',
                'requirements' => 'Open to students with robotics experience.',
                'awards' => 'Robotics kits, scholarships, and internships.',
                'start_date' => Carbon::parse('2025-06-01'),
                'end_date' => Carbon::parse('2025-08-01'),
                'contacts' => 'Email: robotics@olympiad.org, Phone: +1-555-147-2583',
            ],
            [
                'title' => 'Literature Olympiad 2024',
                'status' => 'published',
                'publish' => Publish::Cerrado,
                'description' => 'A competition for students with a passion for literature and writing.',
                'price' => 20,
                'presentation' => 'Includes creative writing and literary analysis.',
                'requirements' => 'Open to all students.',
                'awards' => 'Publishing deals and scholarships.',
                'start_date' => Carbon::parse('2024-05-01'),
                'end_date' => Carbon::parse('2024-07-01'),
                'contacts' => 'Email: lit@olympiad.org, Phone: +1-555-852-9630',
            ]
        ];

        foreach ($olympiads as $olympiad) {
            Olympiads::create($olympiad);
        }
    }
}
