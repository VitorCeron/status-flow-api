<?php

namespace App\Domains\Dashboard\Repositories\Interfaces;

use App\Models\Monitor;
use Illuminate\Database\Eloquent\Collection;

interface DashboardRepositoryInterface
{
    public function countByUserId(string $userId): int;

    public function countByUserIdAndStatus(string $userId, string $status): int;

    /**
     * @return Collection<int, Monitor>
     */
    public function latestByUserId(string $userId, int $limit): Collection;
}
