<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat role dasar
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $guruRole  = Role::firstOrCreate(['name' => 'guru']);
        $ortuRole  = Role::firstOrCreate(['name' => 'ortu']);

        // 2. Buat user admin awal
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'], // key unik
            [
                'name'      => 'Admin Default',
                'password'  => bcrypt('pass1234'), // ganti nanti kalau perlu
                'is_active' => true,
            ],
        );

        // kasih role admin ke user ini
        $admin->assignRole($adminRole);

        // 3. (Opsional tapi bagus) buat beberapa user orang tua supaya StudentFactory bisa dapat parent
        $parents = User::factory()->count(5)->create();
        foreach ($parents as $parent) {
            $parent->assignRole($ortuRole);
        }

        // 4. Panggil seeder lain (yang sudah kamu punya)
        $this->call([
            StudentSeeder::class,
            BookSeeder::class,
            NewsSeeder::class,
        ]);
    }
}
