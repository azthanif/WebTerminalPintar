<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('teacher_notes', 'attendance_id')) {
                $table->foreignId('attendance_id')
                    ->nullable()
                    ->after('schedule_id')
                    ->constrained('attendances')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->unique('attendance_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_notes', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_notes', 'attendance_id')) {
                $table->dropUnique('teacher_notes_attendance_id_unique');
                $table->dropForeign(['attendance_id']);
                $table->dropColumn('attendance_id');
            }
        });
    }
};
