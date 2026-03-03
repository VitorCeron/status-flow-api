<?php

namespace App\Domains\Backoffice\Dashboard\Services;

use App\Domains\Backoffice\Dashboard\Repositories\Interfaces\BackofficeDashboardRepositoryInterface;
use App\Domains\Backoffice\Dashboard\Services\Interfaces\BackofficeDashboardServiceInterface;
use App\Enums\MonitorStatusEnum;

class BackofficeDashboardService implements BackofficeDashboardServiceInterface
{
    /**
     *
     * @param BackofficeDashboardRepositoryInterface $repository
     */
    public function __construct(
        private readonly BackofficeDashboardRepositoryInterface $repository,
    ) {}

    /**
     *
     * @inheritdoc
     */
    public function summary(): array
    {
        return [
            'total_users'    => $this->repository->countUsers(),
            'total_monitors' => $this->repository->countAllMonitors(),
            'total_up'       => $this->repository->countAllMonitorsByStatus(MonitorStatusEnum::UP->value),
            'total_down'     => $this->repository->countAllMonitorsByStatus(MonitorStatusEnum::DOWN->value),
            'last_users'     => $this->repository->latestUsers(5),
            'timezones'      => $this->repository->countUsersByTimezone(),
        ];
    }
}
