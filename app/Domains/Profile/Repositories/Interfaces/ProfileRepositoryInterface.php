<?php

namespace App\Domains\Profile\Repositories\Interfaces;

use App\Models\User;

interface ProfileRepositoryInterface
{
    /**
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateSettings(User $user, array $data): User;
}
