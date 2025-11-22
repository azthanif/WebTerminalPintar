<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // relasi ke user orang tua (boleh null)
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // kode/ID siswa yang ditampilkan di badge (misal: SW001)
            $table->string('student_id', 50)->unique();

            // nama siswa
            $table->string('name', 150);

            // pendidikan terakhir (SD/SMP/SMA/... )
            $table->string('education_level', 50)->nullable();

            // status aktif/nonaktif
            $table->enum('status', ['active', 'inactive'])->default('active');

            // field tambahan opsional
            $table->date('date_of_birth')->nullable();
            $table->string('school_name', 150)->nullable();
            $table->text('address')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
