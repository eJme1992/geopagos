<?php namespace App\Models\Repository\Player;

use App\Models\Player;
use App\Models\Repository\Player\IPlayerRepository;
use App\Models\Repository\Repository as AbstractRepository;
use Illuminate\Support\Collection;

class PlayerRepository extends AbstractRepository implements IPlayerRepository {

	protected $modelClassName = 'App\Models\Player';

	public function getPlayersForGenderSlugs(array $slugs, array $columns = array('*')):?Collection{
		$query  = $this->model->select($columns)->
		join('genders', 'genders.id', '=', 'players.gender_id');
		if(!empty($slugs)){
			$query->whereIn('genders.slug', $slugs);
		}
		return $query->get();
	}

	
	public function getPlayersForGenderSlugNotTournament(array $slugs,int $tournamentId, array $columns = array('*')): ?Collection
	{
		return $this->model->select($columns)
		->join('genders', 'genders.id', '=', 'players.gender_id')
		->leftJoin('tournament_players', function($join) use ($tournamentId) {
			$join->on('tournament_players.player_id', '=', 'players.id')
				 ->where('tournament_players.tournament_id', '=', $tournamentId);
		})
		->whereNull('tournament_players.tournament_id')
		->whereIn('genders.slug', $slugs)
		->get();
	}
}
