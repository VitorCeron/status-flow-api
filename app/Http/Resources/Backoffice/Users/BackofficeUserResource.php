<?php

namespace App\Http\Resources\Backoffice\Users;

use App\Enums\RoleEnum;
use App\Enums\TimezoneEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BackofficeUserResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'timezone'   => $this->timezone instanceof TimezoneEnum
                ? $this->timezone->value
                : $this->timezone,
            'role'       => $this->role instanceof RoleEnum
                ? $this->role->value
                : $this->role,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
