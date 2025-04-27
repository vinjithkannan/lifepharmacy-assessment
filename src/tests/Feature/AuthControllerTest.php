<?php

namespace Tests\Feature;

use App\Services\AuthService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Support\Str;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase; // To refresh the database for each test

    protected $authServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        // Mock AuthService
        $this->authServiceMock = \Mockery::mock(AuthService::class);
        $this->app->instance(AuthService::class, $this->authServiceMock);
    }

    public function test_register_success()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'customer'
        ];

        // Mock the response of the register method
        $this->authServiceMock->shouldReceive('register')
            ->once()
            ->with($userData)
            ->andReturn(Str::random(60)); // Return a fake token

        // Send POST request to the register endpoint
        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['token']);
    }

    public function test_login_success()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        // Mock the login method
        $this->authServiceMock->shouldReceive('login')
            ->once()
            ->with($loginData)
            ->andReturn(Str::random(60)); // Return a fake token

        // Send POST request to the login endpoint
        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['token']);
    }

    public function test_logout_success()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Mock the logout method to not actually log out
        $this->authServiceMock->shouldReceive('logout')
            ->once()
            ->with($user)
            ->andReturn(true);

        // Set user as authenticated
        $this->actingAs($user);

        // Send POST request to the logout endpoint
        $response = $this->postJson('/api/logout');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson(['message' => 'Logged out']);
    }
}
