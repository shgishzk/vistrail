<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->optional()->sentence(6, true),
            'content' => $this->faker->paragraphs(rand(2, 5), true),
            'is_public' => $this->faker->boolean(80),
        ];
    }
}
