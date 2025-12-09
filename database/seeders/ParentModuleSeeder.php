<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeacherNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ParentModuleSeeder extends Seeder
{
    public function run(): void
    {
        $parentRole = Role::firstOrCreate(['name' => 'ortu']);
        $teacherRole = Role::firstOrCreate(['name' => 'guru']);

        $permissions = [
            'parent.dashboard.view',
            'parent.dashboard.notes',
            'parent.dashboard.schedules',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $parentRole->givePermissionTo($permission);
        }

        $parent = User::firstOrCreate(
            ['email' => 'ortu@example.com'],
            [
                'name' => 'Orang Tua Contoh',
                'password' => bcrypt('pass1234'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $parent->assignRole($parentRole);

        $teacher = User::firstOrCreate(
            ['email' => 'guru@example.com'],
            [
                'name' => 'Guru Favorit',
                'password' => bcrypt('pass1234'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $teacher->assignRole($teacherRole);

        $student = Student::firstOrCreate(
            ['student_id' => 'SWP001'],
            [
                'name' => 'Anak Hebat',
                'parent_id' => $parent->id,
                'education_level' => 'SD',
                'status' => 'active',
                'date_of_birth' => '2015-02-01',
                'school_name' => 'SD Terminal Pintar',
                'address' => 'Jl. Pendidikan No. 1',
            ]
        );

        $schedule = Schedule::firstOrCreate(
            [
                'student_id' => $student->id,
                'topic' => 'Kelas Matematika Dasar',
                'start_time' => Carbon::now()->addDay()->setTime(9, 0),
            ],
            [
                'teacher_id' => $teacher->id,
                'subject' => 'Matematika',
                'description' => 'Membahas penjumlahan dan pengurangan dasar',
                'end_time' => Carbon::now()->addDay()->setTime(10, 0),
                'location' => 'Ruang Virtual A',
                'meeting_url' => 'https://meet.example.com/matematika',
                'status' => 'scheduled',
            ]
        );

        $schedule->materials()->firstOrCreate(
            ['title' => 'Modul Matematika Level 1'],
            [
                'description' => 'PDF ringkas terkait materi penjumlahan',
                'download_url' => 'https://example.com/modul-matematika.pdf',
                'file_type' => 'pdf',
            ]
        );

        Attendance::firstOrCreate(
            [
                'student_id' => $student->id,
                'attendance_date' => Carbon::now()->subDay()->toDateString(),
            ],
            [
                'status' => 'Hadir',
                'session_topic' => 'Sains Dasar',
                'session_time' => '08:00 - 09:00',
                'notes' => 'Mengikuti kelas dengan antusias',
            ]
        );

        TeacherNote::firstOrCreate(
            [
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'title' => 'Kemajuan Luar Biasa',
                'recorded_at' => Carbon::now()->subDays(2),
            ],
            [
                'note' => 'Anak menunjukkan peningkatan signifikan pada pemahaman materi.',
                'category' => 'academic',
                'visibility' => 'parent',
            ]
        );
    }
}
