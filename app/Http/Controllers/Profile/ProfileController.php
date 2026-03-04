<?php

namespace App\Http\Controllers\Profile;

use App\Domains\Profile\Services\Interfaces\ProfileServiceInterface;
use App\Enums\TimezoneEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateSettingsRequest;
use App\Http\Resources\Auth\TimezoneResource;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     *
     * @param ProfileServiceInterface $profileService
     */
    public function __construct(
        private readonly ProfileServiceInterface $profileService,
    ) {}

    /**
     *
     * @return JsonResponse
     */
    public function timezones(): JsonResponse
    {
        return TimezoneResource::collection(TimezoneEnum::cases())
            ->response()
            ->setStatusCode(200);
    }

    /**
     *
     * @param UpdateSettingsRequest $request
     * @return JsonResponse
     */
    public function updateSettings(UpdateSettingsRequest $request): JsonResponse
    {
        $user = $this->profileService->updateSettings($request->user(), $request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(200);
    }
}
