<?php

namespace Database\Factories;

use App\Models\TournamentState;
use Illuminate\Database\Eloquent\Factories\Factory;

class TournamentStateFactory extends Factory
{
    protected $model = TournamentState::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
            'description' => $this->faker->sentence,
        ];
    }
}