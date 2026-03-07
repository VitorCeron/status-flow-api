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
    public function jsonOptions(): int
    {
        return JSON_PRESERVE_ZERO_FRACTION;
    }

    /**
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $checksHistory  = $this->resource['checks_history'];
        $statusTimeline = $this->resource['status_timeline'];

        return [
            'response_time_chart' => $this->resource['response_time_chart'],
            'checks_history'      => $checksHistory->map(fn ($log) => [
                'id'               => $log->id,
                'status'           => $log->status->value,
                'response_code'    => $log->response_code,
                'response_time_ms' => $log->response_time_ms,
                'checked_at'       => $log->checked_at,
            ])->values(),
            'status_timeline'     => $statusTimeline->map(fn ($log) => [
                'checked_at' => $log->checked_at,
                'status'     => $log->status->value,
            ])->values(),
            'uptime_percentage'   => (float) $this->resource['uptime_percentage'],
            'last_fail'           => $this->resource['last_fail'],
        ];
    }
}
