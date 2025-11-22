<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();

            // admin pembuat berita
            $table->foreignId('admin_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('title');                   // judul
            $table->string('slug')->unique();          // slug unik
            $table->text('excerpt')->nullable();       // ringkasan
            $table->longText('body');                  // konten lengkap
            $table->string('cover_image_path')->nullable(); // img utama (kalau ada)

            // tipe konten (opsional)
            $table->enum('type', ['news', 'activity', 'gallery'])->default('news');

            // tanggal kegiatan/acara
            $table->date('event_date')->nullable();

            $table->boolean('is_published')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
