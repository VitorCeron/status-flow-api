<?php

namespace App\Domains\Profile\Services\Interfaces;

use App\Models\User;

interface ProfileServiceInterface
{
    /**
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateSettings(User $user, array $data): User;
}
