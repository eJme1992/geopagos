<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\Tournament\ITournamentStateRepository;
use App\Models\Repository\Repository as AbstractRepository;
use App\Models\TournamentState;

class TournamentStateRepository extends AbstractRepository implements ITournamentStateRepository {

	protected $modelClassName = 'App\Models\TournamentState';

	public function findBySlug(string $slug):?TournamentState
	{
		return $this->model->where('slug', $slug)->first();
	}
}
