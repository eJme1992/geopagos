<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\IRepositoryInterface;
use App\Models\TournamentState;

interface ITournamentStateRepository extends IRepositoryInterface
{
    public function findBySlug(string $slug):?TournamentState;
}
