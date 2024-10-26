<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\Tournament;
use App\Models\Repository\Gender\IGenderRepository;
use App\Models\Repository\Player\IPlayerRepository;
use App\Models\Repository\Tournament\ITournamentRepository;
use App\Models\Repository\Tournament\ITournamentPlayerRepository;
use App\Models\Repository\Tournament\ITournamentPlayerStateRepository;
use App\Models\Repository\Tournament\ITournamentStateRepository;

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
    private $playerRepository;
    private $tournamentRepository;
    private $tournamentPlayerRepository;
    private $genderRepository;
    private $tournamentStateRepository;
    private $tournamentPlayerStateRepository;

    const STATE_CREATED  = 'created';
    const STATE_COMPLETE = 'complete';
    const STATE_PENDING  = 'pending';

    public function __construct(
        IPlayerRepository $playerRepository,
        ITournamentRepository $tournamentRepository,
        ITournamentPlayerRepository $tournamentPlayerRepository,
        IGenderRepository $genderRepository,
        ITournamentStateRepository $tournamentStateRepository,
        ITournamentPlayerStateRepository $tournamentPlayerStateRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->tournamentRepository = $tournamentRepository;
        $this->tournamentPlayerRepository = $tournamentPlayerRepository;
        $this->genderRepository = $genderRepository;
        $this->tournamentStateRepository = $tournamentStateRepository;
        $this->tournamentPlayerStateRepository = $tournamentPlayerStateRepository;
    }

    public function register(string $name, string $gender, int $numberPlayers): ?Tournament
    {
        $genderId = $this->getGenderId($gender);
        if (!$genderId || !$this->isValidNumberOfPlayers($numberPlayers)) {
            return null;
        }

        $state = $this->getStateBySlug(self::STATE_CREATED);

        return $this->tournamentRepository->create([
            'name' => $name,
            'gender_id' => $genderId,
            'number_players' => $numberPlayers,
            'state_id' => $state->id,
        ]);
    }

    private function getGenderId(string $gender): ?int
    {
        $gender = $this->genderRepository->findBySlug($gender);
        return $gender ? $gender->id : null;
    }

    public function isValidNumberOfPlayers(int $numberPlayers): bool
    {
        return $numberPlayers > 0 && $numberPlayers % 2 === 0;
    }

    private function getStateBySlug(string $slug)
    {
        return $this->tournamentStateRepository->findBySlug($slug);
    }



    public function registerPlayer(int $tournamentId, int $playerId): bool
    {
        $tournament = $this->tournamentRepository->find($tournamentId);
        $player = $this->playerRepository->find($playerId);

        if (!$tournament || !$player || !$this->isTournamentCreated($tournament) || $this->isPlayerInTournament($tournamentId, $playerId)) {
            return false;
        }

        $state = $this->tournamentPlayerStateRepository->findBySlug(self::STATE_PENDING);
        
        $this->tournamentPlayerRepository->create([
            'tournament_id' => $tournamentId,
            'player_id' => $playerId,
            'state_id' => $state->id,
        ]);

        if ($this->isTournamentComplete($tournamentId)) {
            $state = $this->getStateBySlug(self::STATE_COMPLETE);
            $this->tournamentRepository->update(['state_id' => $state->id], $tournamentId);
        }

        return true;
    }

    public function isTournamentCreated(Tournament $tournament): bool
    {
        return $tournament->state->slug === self::STATE_CREATED;
    }

    public function isPlayerInTournament(int $tournamentId, int $playerId): bool
    {
        return (bool) $this->tournamentPlayerRepository->findByTournamentAndPlayer($tournamentId, $playerId);
    }

    public function isTournamentComplete(int $tournamentId): bool
    {
        $tournament = $this->tournamentRepository->find($tournamentId);
        $players = $this->tournamentPlayerRepository->findByTournament($tournamentId);
        return $players->count() === $tournament->number_players;
    }

    public function getTournamentsByGender(string $gender): ?Collection
    {
        $genderId = $this->getGenderId($gender);
        return $genderId ? $this->tournamentRepository->findByGender($genderId) : null;
    }
}