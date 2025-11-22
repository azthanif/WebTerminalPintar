<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        // pilih user yang role = ortu (kalau ada)
        $parent = User::role('ortu')  // <-- scope dari Spatie HasRoles
            ->inRandomOrder()
            ->first();

        $educationOptions = [
            'Belum Sekolah',
            'SD',
            'SMP',
            'SMA/SMK',
            'Kuliah',
            'Lainnya',
        ];

        return [
            'student_id' => 'SW' . $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->name(),
            'education_level' => $this->faker->randomElement($educationOptions),
            'parent_id' => $parent?->id,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'date_of_birth' => $this->faker->date('Y-m-d'),
            'school_name' => $this->faker->company() . ' School',
            'address' => $this->faker->address(),
        ];
    }
}
