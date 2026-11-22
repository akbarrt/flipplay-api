<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            'title' => ucwords(fake()->unique()->words(3, true)),
            'type' => fake()->randomElement(['film', 'series', 'music', 'game']),
            'genre' => fake()->randomElement(['Action', 'Drama', 'Comedy', 'Horror', 'Sci-Fi', 'Romance']),
            'release_year' => fake()->numberBetween(1990, 2026),
            'description' => fake()->sentence(),
        ];
    }
}
