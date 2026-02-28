<?php

namespace App\Domains\Auth\Services;

use App\Domains\Auth\Repositories\Interfaces\AuthRepositoryInterface;
use App\Domains\Auth\Services\Interfaces\AuthServiceInterface;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    /**
     *
     * @param AuthRepositoryInterface $authRepository
     */
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
    ) {}

    /**
     *
     * @inheritDoc
     */
    public function register(array $data): array
    {
        $user = $this->authRepository->create($data);

        $token = $user->createToken('access_token', ['*'], now()->addMinutes(60));

        return [
            'user' => $user,
            'access_token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ];
    }

    /**
     *
     * @inheritDoc
     */
    public function login(array $credentials): array
    {
        $user = $this->authRepository->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new InvalidCredentialsException();
        }

        $token = $user->createToken('access_token', ['*'], now()->addMinutes(60));

        return [
            'user' => $user,
            'access_token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ];
    }

    /**
     *
     * @inheritDoc
     */
    public function logout(User $user): void
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken $token */
        $token = $user->currentAccessToken();
        $token->delete();
    }

    /**
     *
     * @inheritDoc
     */
    public function me(User $user): User
    {
        return $user;
    }

    /**
     *
     * @inheritDoc
     */
    public function changePassword(User $user, array $data): void
    {
        if (! Hash::check($data['old_password'], $user->password)) {
            throw new InvalidCredentialsException();
        }

        $this->authRepository->updatePassword($user, $data['password']);
    }
}
