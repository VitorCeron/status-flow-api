<?php

namespace App\Domains\MonitorLog\Services\Interfaces;

use App\Models\Monitor;
use App\Models\MonitorLog;

interface MonitorLogServiceInterface
{
    /**
     * Persist a check result log for the given monitor.
     *
     * @param Monitor $monitor
     * @param array $checkResult
     * @return MonitorLog
     */
    public function saveLog(Monitor $monitor, array $checkResult): MonitorLog;

    /**
     * Count consecutive DOWN results for the given monitor.
     *
     * @param Monitor $monitor
     * @return int
     */
    public function countConsecutiveFailures(Monitor $monitor): int;

    /**
     * Delete logs older than the given number of days.
     *
     * @param int $days
     * @return int Number of deleted records
     */
    public function deleteOlderThan(int $days): int;

    /**
     * Return aggregated stats for the given monitor (last 7 days).
     *
     * @param Monitor $monitor
     * @return array
     */
    public function getStats(Monitor $monitor): array;
}
