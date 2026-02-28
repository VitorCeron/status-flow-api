<?php

namespace App\Domains\MonitorLog\Repositories\Interfaces;

use App\Models\MonitorLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface MonitorLogRepositoryInterface
{
    /**
     * @param array $data
     * @return MonitorLog
     */
    public function create(array $data): MonitorLog;

    /**
     * Count consecutive DOWN results for a monitor, stopping at the first UP.
     *
     * @param string $monitorId
     * @return int
     */
    public function countConsecutiveFailures(string $monitorId): int;

    /**
     * Delete logs older than the given number of days.
     *
     * @param int $days
     * @return int Number of deleted records
     */
    public function deleteOlderThan(int $days): int;

    /**
     * Return daily avg/min/max response_time_ms grouped by date for the last N days.
     *
     * @param string $monitorId
     * @param int $days
     * @return Collection
     */
    public function getResponseTimeChart(string $monitorId, int $days = 7): Collection;

    /**
     * Return the last N logs for a monitor ordered by checked_at DESC.
     *
     * @param string $monitorId
     * @param int $limit
     * @return Collection
     */
    public function getLastChecks(string $monitorId, int $limit = 5): Collection;

    /**
     * Return logs for a monitor within the last N days ordered by checked_at DESC.
     *
     * @param string $monitorId
     * @param int $days
     * @return Collection
     */
    public function getLogsForDays(string $monitorId, int $days = 7): Collection;

    /**
     * Return uptime percentage (UP count / total count * 100) for the last N days.
     *
     * @param string $monitorId
     * @param int $days
     * @return float
     */
    public function getUptimePercentage(string $monitorId, int $days = 7): float;

    /**
     * Return the checked_at timestamp of the most recent DOWN log, or null.
     *
     * @param string $monitorId
     * @return Carbon|null
     */
    public function getLastFail(string $monitorId): ?Carbon;
}
