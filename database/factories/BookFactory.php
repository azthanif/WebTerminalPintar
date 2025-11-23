<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $categories = ['Kurikulum', 'Pengayaan', 'Fiksi', 'Non Fiksi', 'Referensi'];
        $status = $this->faker->randomElement([
            Book::STATUS_AVAILABLE,
            Book::STATUS_AVAILABLE,
            Book::STATUS_AVAILABLE,
            Book::STATUS_BORROWED,
        ]);

        $totalStock = $this->faker->numberBetween(2, 10);
        $availableStock = $status === Book::STATUS_BORROWED
            ? max(0, $totalStock - 1)
            : $totalStock;

        return [
            'code'            => 'BK-' . $this->faker->unique()->numerify('####'),
            'title'           => $this->faker->catchPhrase(),
            'author'          => $this->faker->name(),
            'category'        => $this->faker->randomElement($categories),
            'status'          => $status,
            'published_year'  => $this->faker->numberBetween(2000, date('Y')),
            'total_pages'     => $this->faker->numberBetween(120, 480),
            'total_stock'     => $totalStock,
            'available_stock' => $availableStock,
            'description'     => $this->faker->paragraph(),
        ];
    }
}
