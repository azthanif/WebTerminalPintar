<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        DB::statement("ALTER TABLE `attendances` MODIFY COLUMN `status` ENUM('Hadir','Sakit','Izin','Alpha') NULL");
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        DB::statement("ALTER TABLE `attendances` MODIFY COLUMN `status` ENUM('Hadir','Sakit','Izin','Alpha') NOT NULL DEFAULT 'Hadir'");
    }
};
