<?php

namespace Tests\Feature;

use App\Models\Gender;
use App\Models\Attribute;
use App\Models\User;
use App\Helpers\JwtAuth;
use App\Constants\HttpStatusCodes;
use App\Constants\ErrorMessages\GeneralStatusResponse;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Mockery;

// vendor/bin/phpunit --filter AttributeControllerTest 
class AttributeControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $jwtAuth;
    private $token;
    private $attributeRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtAuth = new JwtAuth();
        $this->attributeRepository = Mockery::mock('App\Models\Repository\Attribute\IAttributeRepository');
        $this->app->instance('App\Models\Repository\Attribute\IAttributeRepository', $this->attributeRepository);
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

    public function test_it_retrieves_attributes_by_gender_slug_successfully()
    {
        $gender = 'male';
        $attributes = Attribute::factory()->count(3)->make();

        $this->attributeRepository
            ->shouldReceive('getAttributesByGenderSlug')
            ->with($gender)
            ->andReturn($attributes);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/genders/attributes/{$gender}");

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(200);
        $response->assertJson([
            'status' => GeneralStatusResponse::SUCCESS,
            'data' => $attributes->toArray(),
        ]);
    }

    public function test_it_returns_internal_server_error_on_exception()
    {
        $gender = 'male';

        $this->attributeRepository
            ->shouldReceive('getAttributesByGenderSlug')
            ->with($gender)
            ->andThrow(new \Exception('Internal server error'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/genders/attributes/{$gender}");

        $response->dump(); // Muestra el contenido de la respuesta para depuración

        $response->assertStatus(HttpStatusCodes::INTERNAL_SERVER_ERROR);
        $response->assertJson([
            'error' => 'Internal server error',
        ]);
    }
}