<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Gender;
use App\Helpers\JwtAuth;
use App\Constants\HttpStatusCodes;
use App\Constants\GeneralStatusResponse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Mockery;

class GenderControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $genderRepository;
    private $jwtAuth;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtAuth = new JwtAuth();
        $this->genderRepository = Mockery::mock('App\Models\Repository\Gender\IGenderRepository');
        $this->app->instance('App\Models\Repository\Gender\IGenderRepository', $this->genderRepository);
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

    public function test_get_all_genders_successfully()
    {
        $genders = Gender::factory()->count(3)->make(['slug', 'name']);

        $this->genderRepository
            ->shouldReceive('all')
            ->with(['slug', 'name'])
            ->andReturn($genders);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/genders/all');

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(200);
        $response->assertJson([
            'status' => GeneralStatusResponse::SUCCESS,
            'data' => $genders->toArray(),
        ]);
    }

    public function test_get_all_genders_internal_server_error()
    {
        $this->genderRepository
            ->shouldReceive('all')
            ->with(['slug', 'name'])
            ->andThrow(new \Exception('Internal server error'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/genders/all');

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Internal server error',
        ]);
    }
}