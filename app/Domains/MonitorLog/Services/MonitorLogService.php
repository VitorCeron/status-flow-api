<?php

namespace App\Domains\MonitorLog\Services;

use App\Domains\MonitorLog\Repositories\Interfaces\MonitorLogRepositoryInterface;
use App\Domains\MonitorLog\Services\Interfaces\MonitorLogServiceInterface;
use App\Models\Monitor;
use App\Models\MonitorLog;

class MonitorLogService implements MonitorLogServiceInterface
{
    /**
     * @param MonitorLogRepositoryInterface $monitorLogRepository
     */
    public function __construct(
        private readonly MonitorLogRepositoryInterface $monitorLogRepository,
    ) {}

    /**
     * @inheritDoc
     */
    public function saveLog(Monitor $monitor, array $checkResult): MonitorLog
    {
        return $this->monitorLogRepository->create([
            'monitor_id'       => $monitor->id,
            'status'           => $checkResult['status'],
            'response_code'    => $checkResult['response_code'] ?? null,
            'response_time_ms' => $checkResult['response_time_ms'] ?? null,
            'checked_at'       => $checkResult['checked_at'],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function countConsecutiveFailures(Monitor $monitor): int
    {
        return $this->monitorLogRepository->countConsecutiveFailures($monitor->id);
    }

    /**
     * @inheritDoc
     */
    public function deleteOlderThan(int $days): int
    {
        return $this->monitorLogRepository->deleteOlderThan($days);
    }

    /**
     * @inheritDoc
     */
    public function getStats(Monitor $monitor): array
    {
        return [
            'response_time_chart' => $this->monitorLogRepository->getResponseTimeChart($monitor->id),
            'checks_history'      => $this->monitorLogRepository->getLastChecks($monitor->id, 5),
            'status_timeline'     => $this->monitorLogRepository->getLogsForDays($monitor->id),
            'uptime_percentage'   => $this->monitorLogRepository->getUptimePercentage($monitor->id),
            'last_fail'           => $this->monitorLogRepository->getLastFail($monitor->id),
        ];
    }
}
