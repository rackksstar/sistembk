<?php

namespace Database\Factories;

use App\Models\PostCategory;
use App\Models\Postingan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Postingan>
 */
class PostinganFactory extends Factory
{
    protected $model = Postingan::class;

    public function definition(): array
    {
        $judul = fake()->sentence(4);

        return [
            'post_category_id' => PostCategory::query()->create([
                'name' => fake()->unique()->words(2, true),
                'slug' => Str::slug(fake()->unique()->words(2, true)),
            ])->id,
            'judul' => $judul,
            'slug' => Str::slug($judul).'-'.fake()->unique()->numerify('###'),
            'isi' => fake()->paragraphs(3, true),
            'gambar_path' => null,
            'status' => Postingan::STATUS_PUBLISHED,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => Postingan::STATUS_DRAFT]);
    }
}
