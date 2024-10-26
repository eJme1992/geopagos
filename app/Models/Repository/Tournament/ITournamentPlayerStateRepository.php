<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\IRepositoryInterface;
use App\Models\TournamentPlayerState;

interface ITournamentPlayerStateRepository extends IRepositoryInterface
{
    public function findBySlug(string $slug):?TournamentPlayerState;
}