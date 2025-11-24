<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->nullable()->unique();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('category')->nullable();
            $table->enum('status', ['available', 'borrowed', 'maintenance', 'lost'])->default('available');
            $table->unsignedSmallInteger('published_year')->nullable();
            $table->unsignedSmallInteger('total_pages')->nullable();
            $table->unsignedInteger('total_stock')->default(1);
            $table->unsignedInteger('available_stock')->default(1);
            $table->string('cover_image_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
