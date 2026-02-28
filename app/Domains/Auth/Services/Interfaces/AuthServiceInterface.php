<?php

namespace App\Domains\Auth\Services\Interfaces;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array;

    /**
     *
     * @param array $credentials
     * @return array
     */
    public function login(array $credentials): array;

    /**
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void;

    /**
     *
     * @param User $user
     * @return User
     */
    public function me(User $user): User;

    /**
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    public function changePassword(User $user, array $data): void;
}
