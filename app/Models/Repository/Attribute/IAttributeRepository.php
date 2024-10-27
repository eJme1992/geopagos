<?php namespace App\Models\Repository\Attribute;

use App\Models\Repository\IRepositoryInterface;
use Illuminate\Support\Collection;

interface IAttributeRepository extends IRepositoryInterface
{
    public function getAttributesByGenderSlug(string $slug):? Collection;

    public function getAttributesByGenderId(int $genderId):? Collection;
}
