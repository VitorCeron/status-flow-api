<?php

namespace App\Domains\Backoffice\Users\Services\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface BackofficeUsersServiceInterface
{
    /**
     * Return a paginated list of users applying the given filters.
     *
     * @param integer $perPage
     * @param array   $filters
     * @return LengthAwarePaginator
     */
    public function index(int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * Return a single user by ID.
     *
     * @param string $id
     * @return User
     * @throws \App\Exceptions\Backoffice\UserNotFoundException
     */
    public function show(string $id): User;
}
