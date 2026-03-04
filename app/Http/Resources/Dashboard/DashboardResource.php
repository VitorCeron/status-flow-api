<?php

namespace App\Http\Resources\Dashboard;

use App\Enums\MonitorStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total_monitors' => $this->resource['total_monitors'],
            'total_up'       => $this->resource['total_up'],
            'total_down'     => $this->resource['total_down'],
            'total_paused'   => $this->resource['total_paused'],
            'last_monitors'  => $this->resource['last_monitors']->map(fn ($monitor) => [
                'id'         => $monitor->id,
                'name'       => $monitor->name,
                'url'        => $monitor->url,
                'is_up'      => $monitor->status === MonitorStatusEnum::UP,
                'status'     => $monitor->status->value ?? MonitorStatusEnum::UNKNOWN->value,
                'created_at' => $monitor->created_at,
            ]),
        ];
    }
}
