<?php

namespace App\Domains\MonitorExecution\Services\Interfaces;

use App\Models\Monitor;
use Illuminate\Support\Collection;

interface MonitorExecutionServiceInterface
{
    /**
     * Return all active monitors that are due to run based on their interval.
     *
     * @return Collection<int, Monitor>
     */
    public function getMonitorsDueToRun(): Collection;

    /**
     * Perform the HTTP check for the given monitor.
     *
     * Returns an array with keys:
     *   - status (MonitorStatusEnum value string)
     *   - response_code (int|null)
     *   - response_time_ms (int|null)
     *   - checked_at (Carbon)
     *
     * @param Monitor $monitor
     * @return array
     */
    public function executeCheck(Monitor $monitor): array;

    /**
     * Save the check result, update monitor status, and send email if threshold is crossed.
     *
     * @param Monitor $monitor
     * @param array $checkResult
     * @return void
     */
    public function processResult(Monitor $monitor, array $checkResult): void;
}
