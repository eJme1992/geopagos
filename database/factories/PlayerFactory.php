<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Gender;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'ability' => $this->faker->numberBetween(1, 10),
            'gender_id' => Gender::factory(),
        ];
    }
}