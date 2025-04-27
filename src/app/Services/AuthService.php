<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected AuthRepository $authRepository;

    public function __construct(AuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(array $data): string
    {
        $user = $this->authRepository->createUser($data);
        return $user->createToken('token')->plainTextToken;
    }

    public function login(array $data): string
    {
        $user = $this->authRepository->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken('token')->plainTextToken;
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }
}
