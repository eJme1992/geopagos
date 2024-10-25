<?php

namespace App\Services;

use App\DTOs\PlayerDTO;
use App\Models\Player;
use App\Models\Repository\Player\IPlayerRepository;
use App\Models\Repository\PlayerAttribute\IPlayerAttributeRepository;
use Illuminate\Support\Collection;

/**
 * @OA\Schema(
 *     schema="PlayerService",
 *     type="object",
 *     title="Player Service",
 *     description="Service for managing players"
 * )
 */
class PlayerService
{
    private   $playerRepository;
    private   $playerAttributeRepository;
    

    public function __construct(){
        $this->playerRepository          = app()->make(IPlayerRepository::class);
        $this->playerAttributeRepository = app()->make(IPlayerAttributeRepository::class);
    }

    public function register(PlayerDTO $dto):Player
    {

        $player = $this->playerRepository->create($dto->getPlayerData());

        $attributes = $dto->getAtributesData();

        foreach ($attributes as $attributeId => $value) {
            $this->playerAttributeRepository->create([
                'player_id' => $player->id,
                'attribute_id' => $attributeId,
                'points' => $value
            ]);
        }

        return $player;
    }

    public function getPlayersForGenderSlug(string $slug):?Collection
    {
        return $this->playerRepository->getPlayersForGenderSlugs([$slug],['players.name','players.id']);
    }

    public function getPlayers(): Collection
    {
        return $this->playerRepository->getPlayersForGenderSlugs([]);
    }

    public function getPlayersForGenderSlugNotTournament(string $genderSlug, int $tournamentId):? Collection
    {
        return $this->playerRepository->getPlayersForGenderSlugNotTournament([$genderSlug], $tournamentId,['players.name','players.id']);
    }
  
}
