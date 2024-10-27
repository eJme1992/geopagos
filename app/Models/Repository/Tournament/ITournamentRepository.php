<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\IRepositoryInterface;
use Illuminate\Support\Collection;

interface ITournamentRepository extends IRepositoryInterface
{
	public function findByGender(int $genderId):? Collection;
	public function getTournamentResults(array $filters): ? Collection;
}
