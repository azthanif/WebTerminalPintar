<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $parentRole = Role::firstOrCreate(['name' => 'ortu']);
        $targetTotal = 5;
        $parents = User::role('ortu')->limit($targetTotal)->get();

        if ($parents->count() < $targetTotal) {
            $needed = $targetTotal - $parents->count();
            $newParents = User::factory()
                ->count($needed)
                ->create(['is_active' => true]);

            $newParents->each(fn ($user) => $user->assignRole($parentRole));
            $parents = $parents->concat($newParents);
        }

        $parents = $parents->take($targetTotal)->values();
        $faker = fake();

        foreach ($parents as $index => $parent) {
            Student::updateOrCreate(
                ['student_id' => sprintf('SW%03d', $index + 1)],
                [
                    'parent_id' => $parent->id,
                    'name' => $faker->name(),
                    'education_level' => $faker->randomElement([
                        'Belum Sekolah', 'SD', 'SMP', 'SMA/SMK', 'Kuliah', 'Lainnya',
                    ]),
                    'status' => 'active',
                    'date_of_birth' => $faker->date('Y-m-d', '-8 years'),
                    'school_name' => $faker->company().' School',
                    'address' => $faker->address(),
                ]
            );
        }
    }
}
