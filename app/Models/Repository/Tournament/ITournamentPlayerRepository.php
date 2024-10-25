<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\IRepositoryInterface;
use App\Models\TournamentPlayer;

interface ITournamentPlayerRepository extends IRepositoryInterface
{
    public function findByTournamentAndPlayer(int $tournamentId, int $playerId): ?TournamentPlayer;
}
