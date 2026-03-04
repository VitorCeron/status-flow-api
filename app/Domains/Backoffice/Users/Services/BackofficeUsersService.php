<?php

namespace App\Domains\Backoffice\Users\Services;

use App\Domains\Backoffice\Users\Repositories\Interfaces\BackofficeUsersRepositoryInterface;
use App\Domains\Backoffice\Users\Services\Interfaces\BackofficeUsersServiceInterface;
use App\Exceptions\Backoffice\UserNotFoundException;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class BackofficeUsersService implements BackofficeUsersServiceInterface
{
    /**
     * @param BackofficeUsersRepositoryInterface $repository
     */
    public function __construct(
        private readonly BackofficeUsersRepositoryInterface $repository,
    ) {}

    /**
     * @inheritdoc
     */
    public function index(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $filters);
    }

    /**
     * @inheritdoc
     */
    public function show(string $id): User
    {
        $user = $this->repository->findById($id);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}
