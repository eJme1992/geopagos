<?php

namespace App\Services;

use App\DTOs\PlayerDTO;
use App\Models\Player;
use App\Models\Repository\Player\IPlayerRepository;
use App\Models\Repository\PlayerAttribute\IPlayerAttributeRepository;
use Illuminate\Support\Collection;
use InvalidArgumentException;

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
    private $playerRepository;
    private $playerAttributeRepository;

    public function __construct(
        IPlayerRepository $playerRepository,
        IPlayerAttributeRepository $playerAttributeRepository
    ) {
        $this->playerRepository = $playerRepository;
        $this->playerAttributeRepository = $playerAttributeRepository;
    }

    public function register(PlayerDTO $dto): Player
    {
        $playerData = $dto->getPlayerData();
        $attributes = $dto->getAtributesData();

        $this->validateAttributes($attributes);

        $player = $this->playerRepository->create($playerData);

        foreach ($attributes as $attributeId => $value) {
            $this->playerAttributeRepository->create([
                'player_id' => $player->id,
                'attribute_id' => $attributeId,
                'points' => $value
            ]);
        }

        return $player;
    }

    private function validateAttributes(array $attributes): void
    {
        foreach ($attributes as $attributeId => $value) {
            if (!is_int($attributeId) || !is_numeric($value)) {
                throw new InvalidArgumentException('Invalid attribute data');
            }
        }
    }

    public function getPlayersForGenderSlug(string $slug): ?Collection
    {
        return $this->getPlayersByCriteria([$slug]);
    }

    public function getPlayers(): Collection
    {
        return $this->getPlayersByCriteria([]);
    }

    public function getPlayersForGenderSlugNotTournament(string $genderSlug, int $tournamentId): ?Collection
    {
        return $this->playerRepository->getPlayersForGenderSlugNotTournament(
            [$genderSlug],
            $tournamentId,
            ['players.name', 'players.id']
        );
    }

    private function getPlayersByCriteria(array $slugs): ?Collection
    {
        return $this->playerRepository->getPlayersForGenderSlugs($slugs, ['players.name', 'players.id']);
    }
}