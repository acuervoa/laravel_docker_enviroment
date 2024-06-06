<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Channel;

class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
            'youtube_id' => $this->faker->unique()->regexify('[A-Za-z0-9_-]{11}'),
            'category' => $this->faker->word,
            'subscriber_count' => 0,
            //
        ];
    }
}
