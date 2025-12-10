<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('borrower_name')->nullable()->after('user_id');
            $table->string('borrower_email')->nullable()->after('borrower_name');
        });

        DB::statement('ALTER TABLE loans MODIFY COLUMN user_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['borrower_name', 'borrower_email']);
        });

        DB::statement('ALTER TABLE loans MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL');
    }
};
