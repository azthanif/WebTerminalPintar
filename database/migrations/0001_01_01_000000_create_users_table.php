<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // data dasar
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone', 30)->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // kolom custom yang kamu pakai di model + seeder
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
