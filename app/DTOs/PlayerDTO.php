<?php

namespace App\DTOs;

class PlayerDTO {
    
    private $name;
    private $genderId;
    private $ability;
    private $attributes;

    public function __construct(string $name, int $genderId, int $ability, array $attributes) {
        $this->name        = $name;
        $this->genderId    = $genderId;
        $this->ability     = $ability;
        $this->attributes  = $attributes;
    }

    public function getPlayerData(): array {
        return [
            'name'        => $this->name,
            'gender_id'   => $this->genderId,
            'ability'     => $this->ability,
        ];
    }

    public function getAtributesData(): array {
        return $this->attributes;
    }
}