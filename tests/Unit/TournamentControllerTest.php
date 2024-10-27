<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Gender;
use App\Models\TournamentState;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\TournamentPlayerState;
use App\Helpers\JwtAuth;
use App\Http\Controllers\TournamentController;
use App\Services\TournamentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Mockery;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

class TournamentControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $jwtAuth;
    private $token;
    protected $tournamentService;
    protected $tournamentController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtAuth = new JwtAuth();
        $this->setUpUserWithToken();
        $this->setUpGenderAndState();
        $this->tournamentService = Mockery::mock(TournamentService::class);
        $this->tournamentController = new TournamentController($this->tournamentService);
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

    protected function setUpGenderAndState()
    {
        Gender::updateOrCreate(['slug' => 'male'], ['name' => 'Male']);
        TournamentState::updateOrCreate(
            ['slug' => 'created'], 
            ['name' => 'Created', 'description' => 'Initial state']
        );

        TournamentPlayerState::updateOrCreate(
            ['slug' => 'pending'], 
            ['name' => 'Pending']
        );

        $this->assertDatabaseHas('genders', [
            'slug' => 'male',
            'name' => 'Male',
        ]);

        $this->assertDatabaseHas('tournament_states', [
            'slug' => 'created',
            'name' => 'Created',
        ]);

        $this->assertDatabaseHas('tournament_player_states', [
            'slug' => 'pending',
            'name' => 'Pending',
        ]);
    }

    public function test_it_registers_a_tournament_successfully()
    {
        $data = [
            'gender' => 'male',
            'name' => 'Torneo de Verano',
            'number_players' => 4,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tournaments/register', $data);

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(201);
        $this->assertDatabaseHas('tournaments', [
            'name' => 'Torneo de Verano',
            'number_players' => 4,
        ]);
    }

    public function test_it_returns_error_when_number_of_players_is_invalid()
    {
        $data = [
            'gender' => 'male',
            'name' => 'Torneo Invalido',
            'number_players' => 0,  // Valor no permitido
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tournaments/register', $data);

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'error',
        ]);
    }

    public function test_it_returns_error_when_gender_is_invalid()
    {
        $data = [
            'gender' => 'invalid_gender',
            'name' => 'Torneo de Verano',
            'number_players' => 4,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tournaments/register', $data);

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(422); // Código de estado para error de validación
        $response->assertJsonStructure([
            'errors' => [
                'gender',
            ],
        ]);
    }

    public function test_it_registers_a_player_in_tournament_successfully()
    {
        $tournament = Tournament::factory()->create([
            'gender_id' => Gender::where('slug', 'male')->first()->id,
            'state_id' => TournamentState::where('slug', 'created')->first()->id,
            'number_players' => 4,
        ]);

        $player = Player::factory()->create();

        $data = [
            'tournament_id' => $tournament->id,
            'player_id' => $player->id,
            'state_id' => TournamentPlayerState::where('slug', 'pending')->first()->id, // Proporciona el estado
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tournaments/register-player', $data);

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(201);
        $this->assertDatabaseHas('tournament_players', [
            'tournament_id' => $tournament->id,
            'player_id' => $player->id,
            'state_id' => TournamentPlayerState::where('slug', 'pending')->first()->id, // Verifica el estado
        ]);
    }

    public function test_it_returns_error_when_tournament_is_complete()
    {
        $tournament = Tournament::factory()->create([
            'gender_id' => Gender::where('slug', 'male')->first()->id,
            'state_id' => TournamentState::where('slug', 'created')->first()->id,
            'number_players' => 1,
        ]);

        $player1 = Player::factory()->create();
        $player2 = Player::factory()->create();

        // Registrar el primer jugador
        $tournament->players()->attach($player1->id, ['state_id' => TournamentPlayerState::where('slug', 'pending')->first()->id]);

        $data = [
            'tournament_id' => $tournament->id,
            'player_id' => $player2->id,
            'state_id' => TournamentPlayerState::where('slug', 'pending')->first()->id, // Proporciona el estado
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tournaments/register-player', $data);

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(400);
        $response->assertJsonStructure([
            'error',
        ]);
    }

    public function test_it_start_tournament_successfully()
    {
        $tournament = Tournament::factory()->create([
            'gender_id' => Gender::where('slug', 'male')->first()->id,
            'state_id' => TournamentState::where('slug', 'created')->first()->id,
            'number_players' => 2,
        ]);
    
        $player1 = Player::factory()->create();
        $player2 = Player::factory()->create();
    
        $tournament->players()->attach($player1->id, ['state_id' => TournamentPlayerState::where('slug', 'pending')->first()->id]);
        $tournament->players()->attach($player2->id, ['state_id' => TournamentPlayerState::where('slug', 'pending')->first()->id]);
    
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tournaments/' . $tournament->id . '/start');
    
        $response->dump(); // Muestra el contenido de la respuesta para depuración
    
        $response->assertStatus(200);
    
        $response->assertJsonStructure([
            'status',
            'data' => [
                'id',
                'name',
            ],
        ]);
    }

    public function test_it_fails_to_start_tournament_not_found()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tournaments/999/start'); // ID que no existe
    
        $response->assertStatus(404);
    
        $response->assertJsonStructure([
            'error',
        ]);
    }
    
    public function test_it_fails_to_start_tournament_validation_error()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tournaments/invalid_id/start'); // ID no válido
    
        $response->assertStatus(404);
    
        $response->assertJsonStructure([
            'error',
        ]);
    }
    
    public function test_it_fails_to_start_tournament_internal_server_error()
    {
        // Simular un error interno del servidor
        $this->mock(TournamentService::class, function ($mock) {
            $mock->shouldReceive('startTournament')->andThrow(new \Exception('Internal server error'));
        });
    
        $tournament = Tournament::factory()->create([
            'gender_id' => Gender::where('slug', 'male')->first()->id,
            'state_id' => TournamentState::where('slug', 'created')->first()->id,
            'number_players' => 2,
        ]);
    
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tournaments/' . $tournament->id . '/start');
    
        $response->assertStatus(500);
    
        $response->assertJsonStructure([
            'error',
        ]);
    }

    public function test_get_tournament_results_by_date()
    {
        $date = '2024-10-27';
        $tournaments = Tournament::factory()->count(2)->make(['created_at' => $date]);

        $this->tournamentService->shouldReceive('getTournamentResults')
            ->with(['date' => $date])
            ->andReturn($tournaments);

        $request = Request::create('/api/tournaments/results', 'GET', ['date' => $date]);
        $response = $this->tournamentController->getTournamentResults($request);

        $testResponse = TestResponse::fromBaseResponse($response);
        $testResponse->assertStatus(200);
        $testResponse->assertJson(['status' => 'Success', 'data' => $tournaments->toArray()]);
    }

    public function test_get_tournament_results_by_date_range()
    {
        $startDate = '2024-10-01';
        $endDate = '2024-10-31';
        $tournaments = Tournament::factory()->count(2)->make();

        $this->tournamentService->shouldReceive('getTournamentResults')
            ->with(['start_date' => $startDate, 'end_date' => $endDate])
            ->andReturn($tournaments);

        $request = Request::create('/api/tournaments/results', 'GET', ['start_date' => $startDate, 'end_date' => $endDate]);
        $response = $this->tournamentController->getTournamentResults($request);

        $testResponse = TestResponse::fromBaseResponse($response);
        $testResponse->assertStatus(200);
        $testResponse->assertJson(['status' => 'Success', 'data' => $tournaments->toArray()]);
    }

    public function test_get_tournament_results_by_gender()
    {
        $gender = 'male';
        $tournaments = Tournament::factory()->count(2)->make();

        $this->tournamentService->shouldReceive('getTournamentResults')
            ->with(['gender' => $gender])
            ->andReturn($tournaments);

        $request = Request::create('/api/tournaments/results', 'GET', ['gender' => $gender]);
        $response = $this->tournamentController->getTournamentResults($request);

        $testResponse = TestResponse::fromBaseResponse($response);
        $testResponse->assertStatus(200);
        $testResponse->assertJson(['status' => 'Success', 'data' => $tournaments->toArray()]);
    }

    public function test_get_tournament_results_by_state()
    {
        $state = 'complete';
        $tournaments = Tournament::factory()->count(2)->make();

        $this->tournamentService->shouldReceive('getTournamentResults')
            ->with(['state' => $state])
            ->andReturn($tournaments);

        $request = Request::create('/api/tournaments/results', 'GET', ['state' => $state]);
        $response = $this->tournamentController->getTournamentResults($request);

        $testResponse = TestResponse::fromBaseResponse($response);
        $testResponse->assertStatus(200);
        $testResponse->assertJson(['status' => 'Success', 'data' => $tournaments->toArray()]);
    }

    public function test_get_tournament_results_by_name()
    {
        $name = 'Championship';
        $tournaments = Tournament::factory()->count(2)->make();

        $this->tournamentService->shouldReceive('getTournamentResults')
            ->with(['name' => $name])
            ->andReturn($tournaments);

        $request = Request::create('/api/tournaments/results', 'GET', ['name' => $name]);
        $response = $this->tournamentController->getTournamentResults($request);

        $testResponse = TestResponse::fromBaseResponse($response);
        $testResponse->assertStatus(200);
        $testResponse->assertJson(['status' => 'Success', 'data' => $tournaments->toArray()]);
    }

    public function test_get_tournament_results_by_number_players()
    {
        $numberPlayers = 16;
        $tournaments = Tournament::factory()->count(2)->make();

        $this->tournamentService->shouldReceive('getTournamentResults')
            ->with(['number_players' => $numberPlayers])
            ->andReturn($tournaments);

        $request = Request::create('/api/tournaments/results', 'GET', ['number_players' => $numberPlayers]);
        $response = $this->tournamentController->getTournamentResults($request);

        $testResponse = TestResponse::fromBaseResponse($response);
        $testResponse->assertStatus(200);
        $testResponse->assertJson(['status' => 'Success', 'data' => $tournaments->toArray()]);
    }

    public function test_get_tournament_results_by_winner()
    {
        $winner = 'John';
        $tournaments = Tournament::factory()->count(2)->make();

        $this->tournamentService->shouldReceive('getTournamentResults')
            ->with(['winner' => $winner])
            ->andReturn($tournaments);

        $request = Request::create('/api/tournaments/results', 'GET', ['winner' => $winner]);
        $response = $this->tournamentController->getTournamentResults($request);

        $testResponse = TestResponse::fromBaseResponse($response);
        $testResponse->assertStatus(200);
        $testResponse->assertJson(['status' => 'Success', 'data' => $tournaments->toArray()]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}