<?php

namespace App\Http\Resources\Backoffice\Dashboard;

use App\Enums\TimezoneEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BackofficeDashboardResource extends JsonResource
{
    /**
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'total_users'    => $this->resource['total_users'],
            'total_monitors' => $this->resource['total_monitors'],
            'total_up'       => $this->resource['total_up'],
            'total_down'     => $this->resource['total_down'],
            'last_users'     => $this->resource['last_users']->map(fn ($user) => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'timezone'   => $user->timezone instanceof TimezoneEnum
                    ? $user->timezone->value
                    : $user->timezone,
                'created_at' => $user->created_at,
            ]),
            'timezones'      => $this->resource['timezones']->map(fn ($item) => [
                'timezone' => $item->timezone,
                'total'    => (int) $item->total,
            ]),
        ];
    }
}
