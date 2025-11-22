<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        // ambil admin random (user role admin)
        $admin = User::role('admin')
            ->inRandomOrder()
            ->first();

        $judul = $this->faker->sentence(6);
        $konten = $this->faker->paragraphs(4, true);

        $types = ['news', 'activity', 'gallery'];

        return [
            'admin_id'        => $admin?->id ?? 1,
            'title'           => $judul,
            'slug'            => Str::slug($judul) . '-' . Str::random(4),
            'excerpt'         => Str::limit(strip_tags($konten), 120),
            'body'            => $konten,
            'type'            => $this->faker->randomElement($types),
            'event_date'      => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
            'cover_image_path' => $this->faker->optional()->imageUrl(640, 480, 'nature', true),
            'created_at'      => now()->subDays(rand(1, 90)),
            'updated_at'      => now(),
        ];
    }
}
