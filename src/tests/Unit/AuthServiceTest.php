<?php

namespace Tests\Unit;

use App\Services\AuthService;
use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;
use App\Models\User;

class AuthServiceTest extends TestCase
{
    protected $authRepositoryMock;
    protected $authService;

    public function setUp(): void
    {
        parent::setUp();

        // Mock the AuthRepository
        $this->authRepositoryMock = Mockery::mock(AuthRepository::class);
        $this->app->instance(AuthRepository::class, $this->authRepositoryMock);

        // Create an instance of the AuthService
        $this->authService = new AuthService($this->authRepositoryMock);
    }

    public function test_register_success()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'customer'
        ];

        $user = Mockery::mock(User::class);
        $user->shouldReceive('createToken')->once()->with('token')->andReturnSelf();
        $user->shouldReceive('plainTextToken')->once()->andReturn('mocked-token');

        // Mock the createUser method to return the mocked user
        $this->authRepositoryMock->shouldReceive('createUser')->once()->with($data)->andReturn($user);

        // Call the register method
        $token = $this->authService->register($data);

        // Assert that the returned token is correct
        $this->assertEquals('mocked-token', $token);
    }

    public function test_login_success()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $user = Mockery::mock(User::class);
        $user->shouldReceive('createToken')->once()->with('token')->andReturnSelf();
        $user->shouldReceive('plainTextToken')->once()->andReturn('mocked-token');

        // Mock the findByEmail method to return the mocked user
        $this->authRepositoryMock->shouldReceive('findByEmail')->once()->with($data['email'])->andReturn($user);

        // Mock the Hash::check method to return true for correct password
        Hash::shouldReceive('check')->once()->with($data['password'], $user->password)->andReturn(true);

        // Call the login method
        $token = $this->authService->login($data);

        // Assert that the returned token is correct
        $this->assertEquals('mocked-token', $token);
    }

    public function test_login_failed_due_to_incorrect_password()
    {
        $data = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        // Mock the findByEmail method to return a user
        $user = Mockery::mock(User::class);
        $this->authRepositoryMock->shouldReceive('findByEmail')->once()->with($data['email'])->andReturn($user);

        // Mock the Hash::check method to return false for incorrect password
        Hash::shouldReceive('check')->once()->with($data['password'], $user->password)->andReturn(false);

        // Assert that a ValidationException is thrown
        $this->expectException(ValidationException::class);

        // Call the login method
        $this->authService->login($data);
    }

    public function test_logout_success()
    {
        $user = Mockery::mock(User::class);
        $token = Mockery::mock(\Laravel\Sanctum\NewAccessToken::class);

        // Mock the currentAccessToken method to return the mocked token
        $user->shouldReceive('currentAccessToken')->once()->andReturn($token);

        // Mock the delete method on the token to verify it's called
        $token->shouldReceive('delete')->once();

        // Call the logout method
        $this->authService->logout($user);

        // No assertion needed, we are just verifying that delete was called
    }
}
