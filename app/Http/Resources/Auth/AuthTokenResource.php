<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthTokenResource extends JsonResource
{
    /**
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this['user']),
            'access_token' => $this['access_token'],
            'token_type' => 'Bearer',
            'expires_at' => $this['expires_at'],
        ];
    }
}
