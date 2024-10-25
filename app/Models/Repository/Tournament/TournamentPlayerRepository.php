<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\Tournament\ITournamentPlayerRepository;
use App\Models\Repository\Repository as AbstractRepository;
use App\Models\TournamentPlayer;

class TournamentPlayerRepository extends AbstractRepository implements ITournamentPlayerRepository {
	protected $modelClassName = 'App\Models\TournamentPlayer';

	public function findByTournamentAndPlayer(int $tournamentId, int $playerId): ?TournamentPlayer
    {
        return $this->model->where('tournament_id', $tournamentId)
            ->where('player_id', $playerId)
            ->first();
    }

    public function findByTournament(int $tournamentId)
    {
        return $this->model->where('tournament_id', $tournamentId)->get();
    }

}
