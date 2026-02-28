<?php

namespace App\Domains\MonitorLog\Repositories;

use App\Domains\MonitorLog\Repositories\Interfaces\MonitorLogRepositoryInterface;
use App\Enums\MonitorStatusEnum;
use App\Models\MonitorLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonitorLogRepository implements MonitorLogRepositoryInterface
{
    /**
     * @var MonitorLog
     */
    protected $model;

    /**
     * @param MonitorLog $model
     */
    public function __construct(MonitorLog $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function create(array $data): MonitorLog
    {
        return $this->model->create($data)->fresh();
    }

    /**
     * @inheritDoc
     */
    public function countConsecutiveFailures(string $monitorId): int
    {
        $result = DB::selectOne("
            SELECT COUNT(*) as total
            FROM monitor_logs
            WHERE monitor_id = ?
              AND checked_at > COALESCE(
                (
                    SELECT checked_at
                    FROM monitor_logs
                    WHERE monitor_id = ?
                      AND status != ?
                    ORDER BY checked_at DESC
                    LIMIT 1
                ),
                '1970-01-01'
              )
              AND status = ?
        ", [$monitorId, $monitorId, MonitorStatusEnum::DOWN->value, MonitorStatusEnum::DOWN->value]);

        return (int) ($result->total ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function deleteOlderThan(int $days): int
    {
        return $this->model
            ->where('checked_at', '<', Carbon::now()->subDays($days))
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function getResponseTimeChart(string $monitorId, int $days = 7): Collection
    {
        $since = Carbon::now()->subDays($days)->startOfDay();

        return $this->model
            ->selectRaw('DATE(checked_at) as date, AVG(response_time_ms) as avg_ms, MIN(response_time_ms) as min_ms, MAX(response_time_ms) as max_ms')
            ->where('monitor_id', $monitorId)
            ->where('checked_at', '>=', $since)
            ->whereNotNull('response_time_ms')
            ->groupByRaw('DATE(checked_at)')
            ->orderByRaw('DATE(checked_at) ASC')
            ->get()
            ->map(fn ($row) => [
                'date'   => $row->date,
                'avg_ms' => (int) round($row->avg_ms),
                'min_ms' => (int) $row->min_ms,
                'max_ms' => (int) $row->max_ms,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getLastChecks(string $monitorId, int $limit = 5): Collection
    {
        return $this->model
            ->where('monitor_id', $monitorId)
            ->orderBy('checked_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getLogsForDays(string $monitorId, int $days = 7): Collection
    {
        $since = Carbon::now()->subDays($days)->startOfDay();

        return $this->model
            ->where('monitor_id', $monitorId)
            ->where('checked_at', '>=', $since)
            ->orderBy('checked_at', 'desc')
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function getUptimePercentage(string $monitorId, int $days = 7): float
    {
        $since = Carbon::now()->subDays($days)->startOfDay();

        $total = $this->model
            ->where('monitor_id', $monitorId)
            ->where('checked_at', '>=', $since)
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        $upCount = $this->model
            ->where('monitor_id', $monitorId)
            ->where('checked_at', '>=', $since)
            ->where('status', MonitorStatusEnum::UP->value)
            ->count();

        return round(($upCount / $total) * 100, 2);
    }

    /**
     * @inheritDoc
     */
    public function getLastFail(string $monitorId): ?Carbon
    {
        $log = $this->model
            ->where('monitor_id', $monitorId)
            ->where('status', MonitorStatusEnum::DOWN->value)
            ->orderBy('checked_at', 'desc')
            ->first();

        return $log ? Carbon::parse($log->checked_at) : null;
    }
}
