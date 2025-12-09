<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Material;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeacherNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GuruModuleSeeder extends Seeder
{
    public function run(): void
    {
        $guruRole = Role::firstOrCreate(['name' => 'guru']);

        $permissions = [
            'guru.dashboard.view',
            'guru.schedule.manage',
            'guru.material.manage',
            'guru.attendance.manage',
            'guru.notes.manage',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            if (! $guruRole->hasPermissionTo($permission)) {
                $guruRole->givePermissionTo($permission);
            }
        }

        $teachers = collect([
            [
                'name' => 'Pak Fakhri',
                'email' => 'guru.matematika@terminalpintar.com',
                'specialty' => 'Matematika Dasar',
            ],
            [
                'name' => 'Ibu Ica',
                'email' => 'guru.bahasa@terminalpintar.com',
                'specialty' => 'Bahasa Inggris',
            ],
        ])->map(function (array $profile) use ($guruRole) {
            $teacher = User::firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if (! $teacher->hasRole($guruRole)) {
                $teacher->assignRole($guruRole);
            }

            return $teacher;
        });

        $parent = User::firstOrCreate(
            ['email' => 'orangtua.hanif@terminalpintar.com'],
            [
                'name' => 'Ibu Rezky Nur Amalia',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $student = Student::firstOrCreate(
            ['student_id' => 'SWG001'],
            [
                'name' => 'Hanif Jawahir',
                'parent_id' => $parent->id,
                'education_level' => 'SD',
                'status' => 'active',
                'date_of_birth' => Carbon::parse('2015-01-15'),
                'school_name' => 'SD Terminal Pintar',
            ]
        );

        $baseSchedules = [
            [
                'subject' => 'Matematika',
                'topic' => 'Perkalian Ceria',
                'learning_focus' => 'Operasi bilangan',
                'start_time' => Carbon::now()->addDay()->setTime(9, 0),
                'end_time' => Carbon::now()->addDay()->setTime(11, 0),
                'location' => 'Kelas A - Numeracy Lab',
                'status_badge' => 'Akan Datang',
                'color_hex' => '#f97316',
            ],
            [
                'subject' => 'Bahasa Inggris',
                'topic' => 'Vocabulary - Things Around Us',
                'learning_focus' => 'Vocabulary',
                'start_time' => Carbon::now()->subDay()->setTime(10, 0),
                'end_time' => Carbon::now()->subDay()->setTime(11, 30),
                'location' => 'Kelas B - Language Hub',
                'status_badge' => 'Selesai',
                'color_hex' => '#0ea5e9',
            ],
        ];

        foreach ($baseSchedules as $index => $payload) {
            $teacher = $teachers[$index % $teachers->count()];

            $schedule = Schedule::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'student_id' => $student->id,
                    'topic' => $payload['topic'],
                    'start_time' => $payload['start_time'],
                ],
                array_merge($payload, [
                    'subject' => $payload['subject'],
                    'learning_summary' => Arr::random([
                        'Fokus pada latihan soal berbasis cerita',
                        'Menguatkan kosakata dan pengucapan',
                        'Memastikan siswa nyaman saat diskusi kelompok',
                    ]),
                    'max_participants' => 10,
                    'present_count' => 1,
                    'meeting_url' => 'https://meet.terminalpintar.com/'.md5($payload['topic']),
                    'attachments_meta' => [
                        'handouts' => 2,
                        'videos' => 1,
                    ],
                    'status' => $payload['status_badge'],
                ])
            );

            $schedule->refreshStatusBadge();

            Material::updateOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'title' => $payload['topic'].' - Modul Utama',
                ],
                [
                    'description' => 'Materi pendukung utama untuk sesi '.$payload['topic'],
                    'file_path' => 'materials/'.Str::slug($payload['topic']).'.pdf',
                    'file_type' => 'pdf',
                    'file_size' => 1024,
                    'download_url' => 'https://cdn.terminalpintar.com/materials/'.Str::slug($payload['topic']).'.pdf',
                    'status' => 'Terunggah',
                    'downloads_count' => 12,
                    'uploaded_by' => $teacher->id,
                    'labels' => ['Workbook', 'Utama'],
                ]
            );

            Attendance::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'schedule_id' => $schedule->id,
                    'attendance_date' => $payload['start_time']->toDateString(),
                ],
                [
                    'recorded_by' => $teacher->id,
                    'recorded_at' => $payload['start_time']->copy()->subMinutes(15),
                    'status' => $index === 0 ? 'Hadir' : 'Izin',
                    'session_topic' => $payload['topic'],
                    'session_time' => $payload['start_time']->format('H:i').' - '.$payload['end_time']->format('H:i'),
                    'notes' => $index === 0
                        ? 'Hanif sangat antusias mengikuti sesi'
                        : 'Menghubungi orang tua untuk memastikan alasan izin',
                    'input_channel' => 'web',
                    'requires_follow_up' => $index !== 0,
                    'meta' => ['temperature' => '36.5C'],
                ]
            );

            TeacherNote::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'teacher_id' => $teacher->id,
                    'schedule_id' => $schedule->id,
                    'title' => 'Catatan '.$payload['topic'],
                ],
                [
                    'note' => $index === 0
                        ? 'Hanif sudah memahami konsep dasar, siap naik level.'
                        : 'Perlu latihan pengucapan tambahan di rumah.',
                    'category' => $index === 0 ? 'academic' : 'communication',
                    'visibility' => 'parent',
                    'recorded_at' => $payload['start_time']->copy()->subHours(1),
                    'tag_color' => $payload['color_hex'],
                    'sentiment' => $index === 0 ? 'positive' : 'neutral',
                    'is_flagged' => $index !== 0,
                    'follow_up_actions' => $index === 0
                        ? 'Berikan soal cerita tambahan'
                        : 'Latihan kosa kata harian selama 10 menit',
                    'attachments' => [
                        ['type' => 'image', 'path' => 'notes/'.Str::slug($payload['topic']).'.png'],
                    ],
                ]
            );
        }
    }
}
