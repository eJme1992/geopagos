<?php namespace App\Models\Repository\Tournament;

use App\Models\Repository\Tournament\ITournamentRepository;
use App\Models\Repository\Repository as AbstractRepository;
use Illuminate\Support\Collection;


class TournamentRepository extends AbstractRepository implements ITournamentRepository {

	protected $modelClassName = 'App\Models\Tournament';

	public function findByGender(int $genderId):?Collection {
		return $this->model->where('gender_id',$genderId)->get();
	}

	
}
