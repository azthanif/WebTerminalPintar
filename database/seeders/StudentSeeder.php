<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // buat 50 siswa
        Student::factory()->count(5)->create();
    }
}
