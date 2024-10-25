<?php

namespace App\Strategies;

use App\Http\Requests\FemalePlayerDTO; // DTO para jugadores femeninos

class FemalePlayerValidation implements PlayerValidationStrategy
{
    public function validate(array $data)
    {
        $dto = new FemalePlayerDTO($data);
        // Aquí validas los datos para la jugadora femenina usando el DTO
        return $dto->validated();
    }
}
