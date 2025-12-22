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
        Role::firstOrCreate(['name' => 'guru']);
        Role::firstOrCreate(['name' => 'ortu']);

        // 2. Buat user admin awal saja
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'      => 'Admin Default',
                'password'  => bcrypt('pass1234'),
                'is_active' => true,
            ],
        );

        // kasih role admin ke user ini
        $admin->assignRole($adminRole);

        // 3. Seeder lain (opsional): aktifkan jika diperlukan
        // $this->call([
        //     StudentSeeder::class,
        //     BookSeeder::class,
        //     NewsSeeder::class,
        //     ParentModuleSeeder::class,
        //     GuruModuleSeeder::class,
        // ]);
    }
}
