<?php

namespace App\Domains\Dashboard\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface DashboardRepositoryInterface
{
    /**
     *
     * @param string $userId
     * @return integer
     */
    public function countByUserId(string $userId): int;

    /**
     *
     * @param string $userId
     * @param string $status
     * @return integer
     */
    public function countByUserIdAndStatus(string $userId, string $status): int;

    /**
     *
     * @param string $userId
     * @return integer
     */
    public function countPausedByUserId(string $userId): int;

    /**
     *
     * @param string $userId
     * @param integer $limit
     * @return Collection
     */
    public function latestByUserId(string $userId, int $limit): Collection;
}
