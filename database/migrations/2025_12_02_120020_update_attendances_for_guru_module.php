<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $existingStatuses = Schema::hasColumn('attendances', 'status')
            ? DB::table('attendances')->select('id', 'status')->get()
            : collect();

        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'status')) {
                $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpha'])->default('Hadir');
            }

            if (! Schema::hasColumn('attendances', 'schedule_id')) {
                $table->foreignId('schedule_id')
                    ->nullable()
                    ->after('student_id')
                    ->constrained('schedules')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (! Schema::hasColumn('attendances', 'recorded_by')) {
                $table->foreignId('recorded_by')
                    ->nullable()
                    ->after('schedule_id')
                    ->constrained('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (! Schema::hasColumn('attendances', 'recorded_at')) {
                $table->timestamp('recorded_at')->nullable()->after('attendance_date');
            }

            if (! Schema::hasColumn('attendances', 'input_channel')) {
                $table->string('input_channel', 30)->nullable()->after('session_time');
            }

            if (! Schema::hasColumn('attendances', 'requires_follow_up')) {
                $table->boolean('requires_follow_up')->default(false)->after('notes');
            }

            if (! Schema::hasColumn('attendances', 'meta')) {
                $table->json('meta')->nullable()->after('requires_follow_up');
            }
        });

        $existingStatuses->each(function ($row) {
            DB::table('attendances')
                ->where('id', $row->id)
                ->update([
                    'status' => match ($row->status) {
                        'present' => 'Hadir',
                        'permit' => 'Izin',
                        'sick' => 'Sakit',
                        'absent' => 'Alpha',
                        default => 'Alpha',
                    },
                ]);
        });
    }

    public function down(): void
    {
        $existingStatuses = Schema::hasColumn('attendances', 'status')
            ? DB::table('attendances')->select('id', 'status')->get()
            : collect();

        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'meta')) {
                $table->dropColumn('meta');
            }

            if (Schema::hasColumn('attendances', 'requires_follow_up')) {
                $table->dropColumn('requires_follow_up');
            }

            if (Schema::hasColumn('attendances', 'input_channel')) {
                $table->dropColumn('input_channel');
            }

            if (Schema::hasColumn('attendances', 'recorded_at')) {
                $table->dropColumn('recorded_at');
            }

            if (Schema::hasColumn('attendances', 'recorded_by')) {
                $table->dropForeign(['recorded_by']);
                $table->dropColumn('recorded_by');
            }

            if (Schema::hasColumn('attendances', 'schedule_id')) {
                $table->dropForeign(['schedule_id']);
                $table->dropColumn('schedule_id');
            }

            if (Schema::hasColumn('attendances', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'status')) {
                $table->enum('status', ['present', 'permit', 'sick', 'absent'])->default('present');
            }
        });

        $existingStatuses->each(function ($row) {
            DB::table('attendances')
                ->where('id', $row->id)
                ->update([
                    'status' => match ($row->status) {
                        'Hadir' => 'present',
                        'Izin' => 'permit',
                        'Sakit' => 'sick',
                        'Alpha' => 'absent',
                        default => 'absent',
                    },
                ]);
        });
    }
};
