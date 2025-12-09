<?php

namespace Tests\Feature\Guru;

use App\Models\Schedule;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_can_create_schedule(): void
    {
        $guruRole = Role::firstOrCreate(['name' => 'guru']);

        $guru = User::factory()->create([
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $guru->assignRole($guruRole);

        $student = Student::create([
            'parent_id' => null,
            'student_id' => 'SWTEST01',
            'name' => 'Siswa Uji',
            'education_level' => 'SD',
            'status' => 'active',
        ]);

        $payload = [
            'student_id' => $student->id,
            'subject' => 'Matematika',
            'topic' => 'Kelas Pecahan',
            'learning_focus' => 'Operasi bilangan',
            'start_time' => Carbon::now()->addDay()->setTime(9, 0)->toDateTimeString(),
            'end_time' => Carbon::now()->addDay()->setTime(11, 0)->toDateTimeString(),
            'location' => 'Ruang A',
            'meeting_url' => 'https://meet.example.com/uji',
        ];

        $response = $this->actingAs($guru)
            ->postJson('/guru/api/schedules', $payload);

        $response->assertCreated()
            ->assertJsonFragment(['topic' => 'Kelas Pecahan']);

        $createdSchedule = Schedule::firstWhere('topic', 'Kelas Pecahan');

        $this->assertNotNull($createdSchedule);

        $this->assertDatabaseHas('schedules', [
            'topic' => 'Kelas Pecahan',
            'teacher_id' => $guru->id,
        ]);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'schedule_id' => $createdSchedule->id,
            'status' => 'Hadir',
        ]);
    }
}
