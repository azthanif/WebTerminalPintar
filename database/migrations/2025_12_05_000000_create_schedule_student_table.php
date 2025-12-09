<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')
                ->constrained('schedules')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['schedule_id', 'student_id']);
        });

        DB::table('schedules')
            ->whereNotNull('student_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                $inserts = [];

                foreach ($rows as $row) {
                    $inserts[] = [
                        'schedule_id' => $row->id,
                        'student_id' => $row->student_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($inserts) {
                    DB::table('schedule_student')->insert($inserts);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_student');
    }
};
