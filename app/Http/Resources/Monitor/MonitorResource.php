<?php

namespace App\Http\Resources\Monitor;

use App\Enums\MonitorMethodEnum;
use App\Enums\MonitorStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonitorResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user_id'        => $this->user_id,
            'name'           => $this->name,
            'url'            => $this->url,
            'method'         => $this->method->value ?? MonitorMethodEnum::GET->value,
            'interval'       => $this->interval->value ?? 60,
            'timeout'        => $this->timeout,
            'fail_threshold' => $this->fail_threshold,
            'notify_email'   => $this->notify_email,
            'is_active'      => $this->is_active,
            'status'         => $this->status->value ?? MonitorStatusEnum::UNKNOWN->value,
            'last_checked_at' => $this->last_checked_at,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
