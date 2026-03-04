<?php

namespace App\Domains\Backoffice\Users\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface BackofficeUsersRepositoryInterface
{
    /**
     * Paginate users with optional filters.
     *
     * @param integer $perPage
     * @param array   $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * Find a user by ID including soft-deleted records.
     *
     * @param string $id
     * @return User|null
     */
    public function findById(string $id): ?User;
}
