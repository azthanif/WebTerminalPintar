<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        // contoh: generate 20 berita
        News::factory()->count(5)->create();
    }
}
