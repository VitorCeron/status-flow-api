<?php

namespace App\Domains\Monitor\Services\Interfaces;

use App\Models\Monitor;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface MonitorServiceInterface
{
    /**
     * @param User $user
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function index(User $user, int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * @param User $user
     * @param array $data
     * @return Monitor
     */
    public function create(User $user, array $data): Monitor;

    /**
     * @param User $user
     * @param string $id
     * @return Monitor
     */
    public function show(User $user, string $id): Monitor;

    /**
     * @param User $user
     * @param string $id
     * @param array $data
     * @return Monitor
     */
    public function update(User $user, string $id, array $data): Monitor;

    /**
     * @param User $user
     * @param string $id
     * @return void
     */
    public function delete(User $user, string $id): void;
}
