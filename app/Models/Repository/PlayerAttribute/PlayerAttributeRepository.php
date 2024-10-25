<?php namespace App\Models\Repository\PlayerAttribute;

use App\Models\Repository\PlayerAttribute\IPlayerAttributeRepository;
use App\Models\Repository\Repository as AbstractRepository;


class PlayerAttributeRepository extends AbstractRepository implements IPlayerAttributeRepository {

	protected $modelClassName = 'App\Models\PlayerAttribute';
}
