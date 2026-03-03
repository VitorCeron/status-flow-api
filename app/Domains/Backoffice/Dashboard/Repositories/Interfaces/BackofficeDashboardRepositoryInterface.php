<?php

namespace App\Domains\Backoffice\Dashboard\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface BackofficeDashboardRepositoryInterface
{
    /**
     *
     * @return integer
     */
    public function countAllMonitors(): int;

    /**
     *
     * @param string $status
     * @return integer
     */
    public function countAllMonitorsByStatus(string $status): int;

    /**
     *
     * @return integer
     */
    public function countUsers(): int;

    /**
     *
     * @param integer $limit
     * @return Collection
     */
    public function latestUsers(int $limit): Collection;

    /**
     *
     * @return Collection
     */
    public function countUsersByTimezone(): Collection;
}
