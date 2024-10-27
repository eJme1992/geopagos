<?php namespace App\Models\Repository\Player;

use App\Models\Repository\IRepositoryInterface;
use Illuminate\Support\Collection;


interface IPlayerRepository extends IRepositoryInterface
{
     public function getPlayersForGenderSlugs(array $slugs, array $columns = array('*')):? Collection;
     public function getPlayersForGenderSlugNotTournament(array $slugs,int $tournamentId, array $columns = array('*')): ? Collection;
}
