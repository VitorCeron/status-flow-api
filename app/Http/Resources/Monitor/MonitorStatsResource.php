<?php

namespace App\Http\Resources\Monitor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitorStatsResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $logs = $this->resource['checks_history'];

        return [
            'response_time_chart' => $this->resource['response_time_chart'],
            'checks_history'      => $logs->map(fn ($log) => [
                'id'               => $log->id,
                'status'           => $log->status->value,
                'response_code'    => $log->response_code,
                'response_time_ms' => $log->response_time_ms,
                'checked_at'       => $log->checked_at,
            ])->values(),
            'status_timeline'     => $logs->map(fn ($log) => [
                'checked_at' => $log->checked_at,
                'status'     => $log->status->value,
            ])->values(),
            'uptime_percentage'   => $this->resource['uptime_percentage'],
            'last_fail'           => $this->resource['last_fail'],
        ];
    }
}
