<?php

namespace App\Domains\Dashboard\Repositories;

use App\Domains\Dashboard\Repositories\Interfaces\DashboardRepositoryInterface;
use App\Models\Monitor;
use Illuminate\Database\Eloquent\Collection;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function __construct(private readonly Monitor $model) {}

    public function countByUserId(string $userId): int
    {
        return $this->model->where('user_id', $userId)->count();
    }

    public function countByUserIdAndStatus(string $userId, string $status): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('status', $status)
            ->count();
    }

    public function latestByUserId(string $userId, int $limit): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'url', 'status', 'created_at']);
    }
}
