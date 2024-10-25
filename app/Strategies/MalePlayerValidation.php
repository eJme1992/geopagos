<?php

namespace App\Strategies;

use App\Http\Requests\MalePlayerDTO; // DTO que has creado para jugadores masculinos

class MalePlayerValidation implements PlayerValidationStrategy
{
    public function validate(array $data)
    {
        $dto = new MalePlayerDTO($data);
        // Aquí validas los datos para el jugador masculino usando el DTO
        return $dto->validated();
    }
}
