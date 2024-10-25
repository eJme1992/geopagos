<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\Tournament\ITournamentPlayerStateRepository;
use App\Models\Repository\Repository as AbstractRepository;
use App\Models\TournamentPlayerState;

class TournamentPlayerStateRepository extends AbstractRepository implements ITournamentPlayerStateRepository {
	protected $modelClassName = 'App\Models\TournamentPlayerState';

	public function findBySlug(string $slug):?TournamentPlayerState
	{
		return $this->model->where('slug', $slug)->first();
	}
}
