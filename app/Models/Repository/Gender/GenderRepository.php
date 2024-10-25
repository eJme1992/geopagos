<?php namespace App\Models\Repository\Gender;

use App\Models\Repository\Gender\IGenderRepository;
use App\Models\Repository\Repository as AbstractRepository;
use App\Models\Gender;


class GenderRepository extends AbstractRepository implements IGenderRepository {

	protected $modelClassName = 'App\Models\Gender';

	public function findBySlug(string $slug):?Gender{
		return $this->model->where('slug', $slug)->first();
	}
}
