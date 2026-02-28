<?php

namespace App\Domains\Auth\Repositories\Interfaces;

use App\Models\User;

interface AuthRepositoryInterface
{
    /**
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     *
     * @param User $user
     * @param string $password
     * @return void
     */
    public function updatePassword(User $user, string $password): void;
}
