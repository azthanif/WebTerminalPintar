<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $ids = range(1, 5);

        foreach ($ids as $number) {
            Student::factory()->create([
                'student_id' => sprintf('SW%03d', $number),
            ]);
        }
    }
}
