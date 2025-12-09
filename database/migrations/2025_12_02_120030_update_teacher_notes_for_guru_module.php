<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_notes', 'schedule_id')) {
                $table->foreignId('schedule_id')
                    ->nullable()
                    ->after('student_id')
                    ->constrained('schedules')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (!Schema::hasColumn('teacher_notes', 'tag_color')) {
                $table->string('tag_color', 7)->nullable()->after('category');
            }

            if (!Schema::hasColumn('teacher_notes', 'sentiment')) {
                $table->string('sentiment', 20)->nullable()->after('tag_color');
            }

            if (!Schema::hasColumn('teacher_notes', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false)->after('sentiment');
            }

            if (!Schema::hasColumn('teacher_notes', 'attachments')) {
                $table->json('attachments')->nullable()->after('note');
            }

            if (!Schema::hasColumn('teacher_notes', 'follow_up_actions')) {
                $table->text('follow_up_actions')->nullable()->after('attachments');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_notes', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_notes', 'follow_up_actions')) {
                $table->dropColumn('follow_up_actions');
            }

            if (Schema::hasColumn('teacher_notes', 'attachments')) {
                $table->dropColumn('attachments');
            }

            if (Schema::hasColumn('teacher_notes', 'is_flagged')) {
                $table->dropColumn('is_flagged');
            }

            if (Schema::hasColumn('teacher_notes', 'sentiment')) {
                $table->dropColumn('sentiment');
            }

            if (Schema::hasColumn('teacher_notes', 'tag_color')) {
                $table->dropColumn('tag_color');
            }

            if (Schema::hasColumn('teacher_notes', 'schedule_id')) {
                $table->dropForeign(['schedule_id']);
                $table->dropColumn('schedule_id');
            }
        });
    }
};
