<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Gender;
use App\Models\TournamentState;
use App\Models\Tournament;
use App\Models\Player;
use App\Helpers\JwtAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $jwtAuth;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtAuth = new JwtAuth();
        $this->setUpUserWithToken();
        $this->setUpGenderAndState();
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
        Gender::firstOrCreate(['slug' => 'male'], ['name' => 'Male']);
        TournamentState::firstOrCreate(
            ['slug' => 'created'], 
            ['name' => 'Created'],
            ['description' => 'Initial state']
        );

        $this->assertDatabaseHas('genders', [
            'slug' => 'male',
            'name' => 'Male',
        ]);

        $this->assertDatabaseHas('tournament_states', [
            'slug' => 'created',
            'name' => 'Created',
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
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tournaments/register-player', $data);

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(201);
        $this->assertDatabaseHas('tournament_players', [
            'tournament_id' => $tournament->id,
            'player_id' => $player->id,
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
        $tournament->players()->attach($player1->id);

        $data = [
            'tournament_id' => $tournament->id,
            'player_id' => $player2->id,
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
}