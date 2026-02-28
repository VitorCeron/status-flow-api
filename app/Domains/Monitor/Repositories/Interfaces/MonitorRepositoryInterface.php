<?php

namespace App\Domains\Monitor\Repositories\Interfaces;

use App\Models\Monitor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MonitorRepositoryInterface
{
    /**
     * @param string $userId
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginateByUserId(string $userId, int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * Return all active monitors whose check interval has elapsed.
     *
     * @return Collection<int, Monitor>
     */
    public function findDueToRun(): Collection;

    /**
     * @param string $id
     * @return Monitor|null
     */
    public function findById(string $id): ?Monitor;

    /**
     * @param array $data
     * @return Monitor
     */
    public function create(array $data): Monitor;

    /**
     * @param Monitor $monitor
     * @param array $data
     * @return Monitor
     */
    public function update(Monitor $monitor, array $data): Monitor;

    /**
     * @param Monitor $monitor
     * @return void
     */
    public function delete(Monitor $monitor): void;
}
