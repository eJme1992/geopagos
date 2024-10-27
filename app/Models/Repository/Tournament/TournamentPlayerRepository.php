<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\Tournament\ITournamentPlayerRepository;
use App\Models\Repository\Repository as AbstractRepository;
use App\Models\TournamentPlayer;
use App\Models\TournamentPlayerState;
use Illuminate\Support\Collection;

class TournamentPlayerRepository extends AbstractRepository implements ITournamentPlayerRepository {
	protected $modelClassName = 'App\Models\TournamentPlayer';

	public function findByTournamentAndPlayer(int $tournamentId, int $playerId): ?TournamentPlayer
    {
        return $this->model->where('tournament_id', $tournamentId)
            ->where('player_id', $playerId)
            ->first();
    }

     public function findByTournament(int $tournamentId,array $statesSlug = array()):?Collection
    {
        $query = $this->model->select('tournament_players.*')
        ->where('tournament_id', $tournamentId);
        
        if(!empty($statesSlug)){
            $query->join('tournament_player_states', 'tournament_player_states.id', '=', 'tournament_players.state_id');
            $query->whereIn('tournament_player_states.slug', $statesSlug);
        }
       return $query->get();
    }

    public function updateStatus(int $tournamentId, int $playerId, int $statusId):?TournamentPlayer
    {
      
        $tournamentPlayer = $this->findByTournamentAndPlayer($tournamentId, $playerId);
        $tournamentPlayer->state_id = $statusId;
        $tournamentPlayer->save();
        return $tournamentPlayer;
    }

}
 