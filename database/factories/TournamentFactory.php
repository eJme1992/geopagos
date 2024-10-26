<?php

namespace Database\Factories;

use App\Models\Tournament;
use App\Models\Gender;
use App\Models\TournamentState;
use Illuminate\Database\Eloquent\Factories\Factory;

class TournamentFactory extends Factory
{
    protected $model = Tournament::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'gender_id' => Gender::factory(),
            'state_id' => TournamentState::factory(),
            'number_players' => $this->faker->numberBetween(2, 32),
        ];
    }
}