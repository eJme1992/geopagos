<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\Tournament\ITournamentPlayerRepository;
use App\Models\Repository\Repository as AbstractRepository;
use App\Models\TournamentPlayer;
use Illuminate\Support\Collection;

class TournamentPlayerRepository extends AbstractRepository implements ITournamentPlayerRepository {
	protected $modelClassName = 'App\Models\TournamentPlayer';

	public function findByTournamentAndPlayer(int $tournamentId, int $playerId): ?TournamentPlayer
    {
        return $this->model->where('tournament_id', $tournamentId)
            ->where('player_id', $playerId)
            ->first();
    }

     public function findByTournament(int $tournamentId,array $statesSlug = array('')):?Collection
    {
        $query = $this->model->select('tournament.*')
        ->where('tournament_id', $tournamentId);
        
        if(!empty($statesSlug)){
            $query->join('tournament_states', 'tournament_states.id', '=', 'tournament_players.status');
            $query->whereIn('status', $statesSlug);
        }
        $query->get();
    }

    public function updateStatus(int $tournamentId, int $playerId, string $status):?TournamentPlayer
    {
        $tournamentPlayer = $this->findByTournamentAndPlayer($tournamentId, $playerId);
        $tournamentPlayer->status = $status;
        $tournamentPlayer->save();
        return $tournamentPlayer;
    }

}
 