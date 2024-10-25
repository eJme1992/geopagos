<?php namespace App\Models\Repository\Attribute;

use App\Models\Repository\Attribute\IAttributeRepository;
use App\Models\Repository\Repository as AbstractRepository;
use Illuminate\Support\Collection;


class AttributeRepository extends AbstractRepository implements IAttributeRepository {

	protected $modelClassName = 'App\Models\Attribute';

	 public function getAttributesByGenderSlug(string $slug):?Collection {
			return $this->model->select('attributes.slug','attributes.name')
			->join('genders','genders.id','=','attributes.gender_id')
			->where('genders.slug', $slug)->get();
	}

	public function getAttributesByGenderId(int $genderId):?Collection {
		return $this->model->where('gender_id', $genderId)->get();
	}
}
