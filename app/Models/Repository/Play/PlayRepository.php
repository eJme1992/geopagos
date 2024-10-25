<?php namespace App\Models\Repository\Play;

use App\Models\Repository\Play\IPlayRepository;
use App\Models\Repository\Repository as AbstractRepository;


class PlayRepository extends AbstractRepository implements IPlayRepository {

	protected $modelClassName = 'App\Models\Play';
}
