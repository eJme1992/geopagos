<?php

namespace App\Factories;

use App\DTOs\PlayerDTO;
use App\Models\Attribute;
use App\Models\Gender;
use App\Models\Repository\Attribute\IAttributeRepository;
use InvalidArgumentException;

class PlayerDTOFactory {

    private $attributeRepository;

    public function __construct() {
        $this->attributeRepository = app()->make(IAttributeRepository::class);
    }
    
    
    public function createPlayerDTO(string $name, string $ability ,Gender $gender, array $attributesPlayer): ?PlayerDTO {
       
            $attributes = $this->attributeRepository->getAttributesByGenderId($gender->id);


            $newAttributes = [];
            foreach ($attributes as $attribute) {
                if (!array_key_exists($attribute->slug, $attributesPlayer)) {
                    return null;
                }
                $newAttributes[$attribute->id] = $attributesPlayer[$attribute->slug];
            }
         
           return new PlayerDTO($name, $gender->id, $ability, $newAttributes);
    }
}