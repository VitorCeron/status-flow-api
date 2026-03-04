<?php

namespace App\Domains\Dashboard\Services;

use App\Domains\Dashboard\Repositories\Interfaces\DashboardRepositoryInterface;
use App\Domains\Dashboard\Services\Interfaces\DashboardServiceInterface;
use App\Enums\MonitorStatusEnum;
use App\Models\User;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private readonly DashboardRepositoryInterface $dashboardRepository,
    ) {}

    /**
     *
     * @param User $user
     * @return array
     */
    public function summary(User $user): array
    {
        $lastMonitors = $this->dashboardRepository->latestByUserId($user->id, 5);

        return [
            'total_monitors' => $this->dashboardRepository->countByUserId($user->id),
            'total_up'       => $this->dashboardRepository->countByUserIdAndStatus($user->id, MonitorStatusEnum::UP->value),
            'total_down'     => $this->dashboardRepository->countByUserIdAndStatus($user->id, MonitorStatusEnum::DOWN->value),
            'total_paused'   => $this->dashboardRepository->countPausedByUserId($user->id),
            'last_monitors'  => $lastMonitors,
        ];
    }
}
