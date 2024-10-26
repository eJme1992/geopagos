<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Gender;
use App\Models\Player;
use App\DTOs\PlayerDTO;
use App\Helpers\JwtAuth;
use App\Constants\HttpStatusCodes;
use App\Constants\ErrorMessages\GeneralStatusResponse;
use App\Constants\ErrorMessages\GeneralErrorMessages;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Tests\TestCase;
use Mockery;

class PlayerControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $jwtAuth;
    private $token;
    private $playerService;
    private $genderRepository;
    private $playerDTOFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtAuth = new JwtAuth();
        $this->playerService = Mockery::mock('App\Services\PlayerService');
        $this->genderRepository = Mockery::mock('App\Models\Repository\Gender\IGenderRepository');
        $this->playerDTOFactory = Mockery::mock('App\Factories\PlayerDTOFactory');
        $this->app->instance('App\Services\PlayerService', $this->playerService);
        $this->app->instance('App\Models\Repository\Gender\IGenderRepository', $this->genderRepository);
        $this->app->instance('App\Factories\PlayerDTOFactory', $this->playerDTOFactory);
        $this->setUpUserWithToken();
    }

    protected function setUpUserWithToken()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->jwtAuth->signup($user->email, 'password123');
        if (is_null($response)) {
            $this->fail('Unable to generate token for the test user.');
        }

        $this->token = $response['token'];
    }

    public function test_register_player_successfully()
    {
        $data = [
            'gender' => 'male',
            'name' => 'John Doe',
            'ability' => 75,
            'atributesPlayer' => [
                'strength' => 5,
                'speed' => 5,
            ],
        ];

        $gender = Gender::firstOrCreate(['slug' => 'male'], ['name' => 'Male']);
        $playerDTO = new PlayerDTO('John Doe', $gender->id, 75, $data['atributesPlayer']);
        $player = Player::factory()->make($data);

        $this->genderRepository
            ->shouldReceive('findBySlug')
            ->with('male')
            ->andReturn($gender);

        $this->playerDTOFactory
            ->shouldReceive('createPlayerDTO')
            ->with('John Doe', 75, $gender, $data['atributesPlayer'])
            ->andReturn($playerDTO);

        $this->playerService
            ->shouldReceive('register')
            ->with($playerDTO)
            ->andReturn($player);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/players/register', $data);

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(200);
        $response->assertJson([
            'status' => GeneralStatusResponse::SUCCESS,
            'data' => $player->toArray(),
        ]);
    }

    public function test_register_player_validation_error()
    {
        $data = [
            'gender' => '',
            'name' => '',
            'ability' => 0,
            'atributesPlayer' => [],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/players/register', $data);

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(HttpStatusCodes::UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure([
            'errors' => [
                'gender',
                'name',
                'ability',
                'atributesPlayer',
            ],
        ]);
    }

    public function test_get_players_for_gender_successfully()
    {
        $gender = 'male';
        $players = Player::factory()->count(3)->make();
        $playersCollection = new Collection($players);

        $this->playerService
            ->shouldReceive('getPlayersForGenderSlug')
            ->with($gender)
            ->andReturn($playersCollection);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/players/gender/{$gender}");

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(200);
        $response->assertJson([
            'status' => GeneralStatusResponse::SUCCESS,
            'data' => $players->toArray(),
        ]);
    }

    public function test_get_players_for_gender_not_found()
    {
        $gender = 'famale';

        $this->playerService
            ->shouldReceive('getPlayersForGenderSlug')
            ->with($gender)
            ->andReturn(new Collection());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/players/gender/{$gender}");

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(HttpStatusCodes::NOT_FOUND);
        $response->assertJson([
            'status' => GeneralStatusResponse::ERROR,
            'message' => GeneralErrorMessages::NO_PLAYERS_FOUND,
        ]);
    }

    public function test_get_players_for_gender_not_tournament_successfully()
    {
        $gender = 'male';
        $tournamentId = 1;
        $players = Player::factory()->count(3)->make();
        $playersCollection = new Collection($players);

        $this->playerService
            ->shouldReceive('getPlayersForGenderSlugNotTournament')
            ->with($gender, $tournamentId)
            ->andReturn($playersCollection);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/players/gender/nottournament/{$gender}/{$tournamentId}");

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(200);
        $response->assertJson([
            'status' => GeneralStatusResponse::SUCCESS,
            'data' => $players->toArray(),
        ]);
    }

    public function test_get_players_for_gender_not_tournament_not_found()
    {
        $gender = 'male';
        $tournamentId = 1;

        $this->playerService
            ->shouldReceive('getPlayersForGenderSlugNotTournament')
            ->with($gender, $tournamentId)
            ->andReturn(new Collection());

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/players/gender/nottournament/{$gender}/{$tournamentId}");

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(HttpStatusCodes::NOT_FOUND);
        $response->assertJson([
            'status' => GeneralStatusResponse::ERROR,
            'message' => GeneralErrorMessages::NO_PLAYERS_FOUND,
        ]);
    }
}