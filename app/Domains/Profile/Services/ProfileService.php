<?php

namespace App\Domains\Profile\Services;

use App\Domains\Profile\Repositories\Interfaces\ProfileRepositoryInterface;
use App\Domains\Profile\Services\Interfaces\ProfileServiceInterface;
use App\Models\User;

class ProfileService implements ProfileServiceInterface
{
    /**
     *
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        private readonly ProfileRepositoryInterface $profileRepository,
    ) {}

    /**
     *
     * @inheritDoc
     */
    public function updateSettings(User $user, array $data): User
    {
        return $this->profileRepository->updateSettings($user, $data);
    }
}
