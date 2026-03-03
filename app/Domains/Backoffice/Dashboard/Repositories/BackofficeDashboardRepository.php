<?php

namespace App\Domains\Backoffice\Dashboard\Repositories;

use App\Domains\Backoffice\Dashboard\Repositories\Interfaces\BackofficeDashboardRepositoryInterface;
use App\Enums\RoleEnum;
use App\Models\Monitor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BackofficeDashboardRepository implements BackofficeDashboardRepositoryInterface
{
    /**
     *
     * @inheritdoc
     */
    public function __construct(
        private readonly Monitor $monitorModel,
        private readonly User $userModel,
    ) {}

    /**
     *
     * @inheritdoc
     */
    public function countAllMonitors(): int
    {
        return $this->monitorModel->count();
    }

    /**
     *
     * @inheritdoc
     */
    public function countAllMonitorsByStatus(string $status): int
    {
        return $this->monitorModel->where('status', $status)->count();
    }

    /**
     *
     * @inheritdoc
     */
    public function countUsers(): int
    {
        return $this->userModel->where('role', RoleEnum::USER->value)->count();
    }

    /**
     *
     * @inheritdoc
     */
    public function latestUsers(int $limit): Collection
    {
        return $this->userModel
            ->where('role', RoleEnum::USER->value)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'timezone', 'created_at']);
    }

    /**
     *
     * @inheritdoc
     */
    public function countUsersByTimezone(): Collection
    {
        return $this->userModel
            ->where('role', RoleEnum::USER->value)
            ->selectRaw('timezone, COUNT(*) as total')
            ->groupBy('timezone')
            ->get();
    }
}
