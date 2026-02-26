<?php

namespace App\Http\Resources\Auth;

use App\Enums\RoleEnum;
use App\Enums\TimezoneEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'timezone' => $this->timezone->value ?? TimezoneEnum::UTC->value,
            'role' => $this->role->value ?? RoleEnum::USER->value,
            'created_at' => $this->created_at,
        ];
    }
}
