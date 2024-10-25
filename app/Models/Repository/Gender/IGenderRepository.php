<?php namespace App\Models\Repository\Gender;

use App\Models\Repository\IRepositoryInterface;

use App\Models\Gender;

interface IGenderRepository extends IRepositoryInterface
{
     public function findBySlug(string $id):?Gender;
}
