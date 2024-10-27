<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\IRepositoryInterface;
use App\Models\TournamentPlayer;
use Illuminate\Support\Collection;

interface ITournamentPlayerRepository extends IRepositoryInterface
{
    public function findByTournamentAndPlayer(int $tournamentId, int $playerId): ?TournamentPlayer;
    public function findByTournament(int $tournamentId,array $statesSlug = array('')):?Collection;
    public function updateStatus(int $tournamentId, int $playerId, string $status):?TournamentPlayer;
}
