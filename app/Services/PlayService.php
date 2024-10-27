<?php

namespace App\Services;

use App\Constants\TournamentPlayersStatus;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\Repository\Play\IPlayRepository;
use App\Models\Repository\Player\IPlayerRepository;
use App\Models\Repository\Tournament\ITournamentPlayerRepository;
use App\Models\Repository\Tournament\ITournamentPlayerStateRepository;
use App\Models\Repository\Attribute\IAttributeRepository;
use InvalidArgumentException;

/**
 * @OA\Schema(
 *     schema="PlayService",
 *     type="object",
 *     title="Play Service",
 *     description="Service for managing Plays"
 * )
 */
class PlayService
{
    private $playerRepository;
    private $tournamentPlayerRepository;
    private $tournamentPlayerStateRepository;
    private $playRepository;
    private $attributesRepository;
    private $details = [];

    public function __construct(
        IPlayerRepository $playerRepository,
        ITournamentPlayerRepository $tournamentPlayerRepository,
        ITournamentPlayerStateRepository $tournamentPlayerStateRepository,
        IPlayRepository $playRepository,
        IAttributeRepository $attributesRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->tournamentPlayerRepository = $tournamentPlayerRepository;
        $this->tournamentPlayerStateRepository = $tournamentPlayerStateRepository;
        $this->playRepository = $playRepository;
        $this->attributesRepository = $attributesRepository;
    }

    public function play(int $player1Id, int $player2Id, Tournament $tournament, int $round): void
    {
        $this->details = [];
        $player1 = $this->playerRepository->find($player1Id);
        $player2 = $this->playerRepository->find($player2Id);

        if (!$player1 || !$player2) {
            throw new InvalidArgumentException('Invalid player IDs provided.');
        }

        $winnerId = $this->getWinnerId($player1, $player2, $tournament->gender_id);
        $loserId = $winnerId === $player1->id ? $player2->id : $player1->id;

        $this->updatePlayerStatus($tournament->id, $winnerId, TournamentPlayersStatus::WINNER);
        $this->updatePlayerStatus($tournament->id, $loserId, TournamentPlayersStatus::LOSER);

        $this->playRepository->create([
            'player1_id'    => $player1Id,
            'player2_id'    => $player2Id,
            'tournament_id' => $tournament->id,
            'winner_id'     => $winnerId,
            'loser_id'      => $loserId,
            'round'         => $round,
            'details'       => json_encode($this->details)
        ]);
    }

    private function getWinnerId(Player $player1, Player $player2, int $genderId): int
    {
        $points = [
            $player1->id => 0,
            $player2->id => 0
        ];

        $attributes = $this->attributesRepository->getAttributesByGenderId($genderId);

        foreach ($attributes as $attribute) {
            $playerAttribute1 = $player1->attributes->firstWhere('slug', $attribute->slug)->pivot->points ?? 0;
            $playerAttribute2 = $player2->attributes->firstWhere('slug', $attribute->slug)->pivot->points ?? 0;
        
            $winner = $this->comparePlayers($playerAttribute1, $player1->ability, $player1->id, $playerAttribute2, $player2->ability, $player2->id);
            $points[$winner]++;
        }

        return $points[$player1->id] > $points[$player2->id] ? $player1->id : $player2->id;
    }

    private function comparePlayers(int $attribute1, int $ability1, int $player1Id, int $attribute2, int $ability2, int $player2Id): int
    {
        $score1 = $this->calculateScore($attribute1, $ability1, $player1Id);
        $score2 = $this->calculateScore($attribute2, $ability2, $player2Id);

        return $score1 >= $score2 ? $player1Id : $player2Id;
    }

    private function calculateScore(int $attributePoints, int $ability, int $playerId): int
    {
        $this->details[] = [
            'playerId' => $playerId,
            'attributePoints' => $attributePoints,
            'ability' => $ability
        ];

        return $attributePoints + rand(intval($ability / 2), $ability);
    }

    private function updatePlayerStatus(int $tournamentId, int $playerId, string $statusSlug): void
    {
        $statusId = $this->tournamentPlayerStateRepository->findBySlug($statusSlug)->id;
        $this->tournamentPlayerRepository->updateStatus($tournamentId, $playerId, $statusId);
    }
}