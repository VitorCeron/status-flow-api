<?php

namespace App\Domains\Profile\Repositories;

use App\Domains\Profile\Repositories\Interfaces\ProfileRepositoryInterface;
use App\Models\User;

class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @var User
     */
    protected $model;

    /**
     * ProfileRepository constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     *
     * @inheritdoc
     */
    public function updateSettings(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }
}
