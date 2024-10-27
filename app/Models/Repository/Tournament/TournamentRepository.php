<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\Tournament\ITournamentRepository;
use App\Models\Repository\Repository as AbstractRepository;
use Illuminate\Support\Collection;
use App\Models\Tournament;


class TournamentRepository extends AbstractRepository implements ITournamentRepository {

	protected $modelClassName = 'App\Models\Tournament';

	public function findByGender(int $genderId):?Collection {
		return $this->model->where('gender_id',$genderId)->get();
	}


	public function getTournamentResults(array $filters): Collection
    {
        $query = Tournament::query()
            ->join('genders', 'tournaments.gender_id', '=', 'genders.id')
            ->join('tournament_states', 'tournaments.state_id', '=', 'tournament_states.id')
            ->leftJoin('players as winners', 'tournaments.winner_id', '=', 'winners.id')
            ->select('tournaments.*', 'genders.name as gender_name', 'tournament_states.name as state_name', 'winners.name as winner_name');

        if (isset($filters['date'])) {
            $query->whereDate('tournaments.created_at', $filters['date']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('tournaments.created_at', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['gender'])) {
            $query->where('genders.slug', $filters['gender']);
        }

        if (isset($filters['state'])) {
            $query->where('tournament_states.slug', $filters['state']);
        }

        if (isset($filters['name'])) {
            $query->where('tournaments.name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['number_players'])) {
            $query->where('tournaments.number_players', $filters['number_players']);
        }

        if (isset($filters['winner'])) {
            $query->where('winners.name', 'like', '%' . $filters['winner'] . '%');
        }

        return $query->where('tournament_states.slug', 'complete')->get();
    }

	
}
