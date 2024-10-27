<?php

namespace Database\Factories;

use App\Models\TournamentPlayerState;
use Illuminate\Database\Eloquent\Factories\Factory;

class TournamentPlayerStateFactory extends Factory
{
    protected $model = TournamentPlayerState::class;

    public function definition()
    {
        return [
            'slug' => $this->faker->unique()->word,
            'name' => $this->faker->word,
        ];
    }
}