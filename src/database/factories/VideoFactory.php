<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Video;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'channel_id' => \App\Models\Channel::factory(),
            'title' => $this->faker->sentence,
            'youtube_id' => $this->faker->unique()->regexify('[A-Za-z0-9_-]{11}'),
            'like_count' => $this->faker->numberBetween(0,10000),
            'published_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'watched' => $this->faker->boolean,
            'rating' => $this->faker->numberBetween(1,5),
        ];
    }
}
