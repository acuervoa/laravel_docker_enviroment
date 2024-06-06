<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\VideoList;

class VideoListFactory extends Factory
{
    protected $model = VideoList::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
        ];
    }
}

