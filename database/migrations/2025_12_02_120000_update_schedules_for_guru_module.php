<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'learning_focus')) {
                $table->string('learning_focus')->nullable()->after('topic');
            }

            if (!Schema::hasColumn('schedules', 'status_badge')) {
                $table->string('status_badge', 30)->default('Akan Datang')->after('status');
            }

            if (!Schema::hasColumn('schedules', 'status_locked_at')) {
                $table->timestamp('status_locked_at')->nullable()->after('status_badge');
            }

            if (!Schema::hasColumn('schedules', 'max_participants')) {
                $table->unsignedSmallInteger('max_participants')->nullable()->after('location');
            }

            if (!Schema::hasColumn('schedules', 'present_count')) {
                $table->unsignedSmallInteger('present_count')->default(0)->after('max_participants');
            }

            if (!Schema::hasColumn('schedules', 'attachments_meta')) {
                $table->json('attachments_meta')->nullable()->after('meeting_url');
            }

            if (!Schema::hasColumn('schedules', 'color_hex')) {
                $table->string('color_hex', 9)->nullable()->after('attachments_meta');
            }

            if (!Schema::hasColumn('schedules', 'learning_summary')) {
                $table->text('learning_summary')->nullable()->after('color_hex');
            }
        });

        if (!Schema::hasColumn('schedules', 'deleted_at')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('schedules', 'deleted_at')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        Schema::table('schedules', function (Blueprint $table) {
            $columns = [
                'learning_focus',
                'status_badge',
                'status_locked_at',
                'max_participants',
                'present_count',
                'attachments_meta',
                'color_hex',
                'learning_summary',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('schedules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
