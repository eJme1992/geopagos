<?php

namespace App\Services;


use Illuminate\Support\Collection;

use App\DTOs\PlayerDTO;
use App\Models\Player;
use App\Models\Repository\Gender\IGenderRepository;
use App\Models\Repository\Player\IPlayerRepository;
use App\Models\Repository\PlayerAttribute\IPlayerAttributeRepository;
use App\Models\Repository\Tournament\ITournamentRepository;
use App\Models\Repository\Tournament\ITournamentPlayerRepository;
use App\Models\Repository\Tournament\ITournamentPlayerStateRepository;
use App\Models\Repository\Tournament\ITournamentStateRepository;
use App\Models\Tournament;

/**
 * @OA\Schema(
 *     schema="TournamentService",
 *     type="object",
 *     title="Tournament Service",
 *     description="Service for managing Tournament"
 * )
 */
class TournamentService
{
    private   $playerRepository;
    private   $tournamentRepository;
    private   $tournamentPlayerRepository;
    private   $genderRepository;
    private   $tournamentStateRepository;
    private   $tournamentPlayerStateRepository;

    public function __construct(){
        $this->playerRepository                = app()->make(IPlayerRepository::class);
        $this->tournamentRepository            = app()->make(ITournamentRepository::class);
        $this->tournamentPlayerRepository      = app()->make(ITournamentPlayerRepository::class);
        $this->genderRepository                = app()->make(IGenderRepository::class);
        $this->tournamentStateRepository       = app()->make(ITournamentStateRepository::class);
        $this->tournamentPlayerStateRepository = app()->make(ITournamentPlayerStateRepository::class);
    }

    public function register(string $name,string $gender,int $numberPlayers):?Tournament
    {

        $genderId = $this->genderRepository->findBySlug($gender)->id;

        if(!$genderId) {
            return null;
        }

         if (!$this->numberPlayersValidate($numberPlayers)) {
              return null;
         }

        $state = $this->tournamentStateRepository->findBySlug('created'); // para pasar a constante

        return  $this->tournamentRepository->create(
                [
                    'name'           => $name,
                    'gender_id'      => $genderId,
                    'number_players' => $numberPlayers,
                    'state_id'       => $state->id
                ]
        );
    }

    public function numberPlayersValidate(int $number_players):bool
    {
        if ($number_players % 2 != 0 || $number_players <= 0) {
            return false;
        }
        return true;
    }

    public function tournamentAndPlayerExist(int $tournamentId, int $playerId):bool
    {
        $playerTournament = $this->tournamentPlayerRepository->findByTournamentAndPlayer($tournamentId, $playerId);

        if ($playerTournament) {
            return true;
        }

        return false;
    }

    public function registerPlayer(int $tournamentId, int $playerId):bool
    {
        $tournament = $this->tournamentRepository->find($tournamentId);
        $player     = $this->playerRepository->find($playerId);

        if (!$tournament || !$player) {
            return false;
        }
        
        if (!$this->tournamentIsCreated($tournamentId)) {
            return false;
        }

        if ($this->tournamentAndPlayerExist($tournamentId, $playerId)) {
            return false;
        }

        $state = $this->tournamentPlayerStateRepository->findBySlug('pending'); // pending, winner, loser pasar a constante

        $this->tournamentPlayerRepository->create(
            [
                'tournament_id' => $tournamentId,
                'player_id'     => $playerId,
                'state_id'      => $state->id
            ]
        );

        if ($this->tournamentIsComplete($tournamentId)) {
            $state = $this->tournamentStateRepository->findBySlug('complete'); // pending, winner, loser pasar a constante
            $this->tournamentRepository->update(
                [
                    'state_id'      => $state->id
                ],$tournamentId
            );
        }

        return true;
    }

    
    public function tournamentIsCreated(int $tournamentId):bool
    {
        $tournament = $this->tournamentRepository->find($tournamentId);
        if ($tournament->state->slug == 'created') {
            return true;
        }
        return false;
    }

    public function tournamentIsComplete(int $tournamentId):bool
    {
        $tournament = $this->tournamentRepository->find($tournamentId);
        $players = $this->tournamentPlayerRepository->findByTournament($tournamentId);
        if ($players->count() == $tournament->number_players) {
            return true;
        }
        return false;
    }

    public function getTournamentsByGender(string $gender):?Collection
    {
        $genderId = $this->genderRepository->findBySlug($gender)->id;
        if(!$genderId) {
            return null;
        }
        return $this->tournamentRepository->findByGender($genderId);
    }

  
}
