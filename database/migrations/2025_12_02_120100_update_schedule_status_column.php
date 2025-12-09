<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE schedules MODIFY status VARCHAR(30) DEFAULT 'Akan Datang'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE schedules MODIFY status ENUM('scheduled','completed','canceled') DEFAULT 'scheduled'");
    }
};
