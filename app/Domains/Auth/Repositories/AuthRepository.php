<?php

namespace App\Domains\Auth\Repositories;

use App\Domains\Auth\Repositories\Interfaces\AuthRepositoryInterface;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * @var User
     */
    protected $model;

    /**
     * AuthRepository constructor.
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
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     *
     * @inheritdoc
     */
    public function create(array $data): User
    {
        return $this->model->create($data);
    }
}
