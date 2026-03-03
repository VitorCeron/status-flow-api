<?php

namespace App\Http\Controllers\Backoffice\Dashboard;

use App\Domains\Backoffice\Dashboard\Services\Interfaces\BackofficeDashboardServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\Dashboard\BackofficeDashboardRequest;
use App\Http\Resources\Backoffice\Dashboard\BackofficeDashboardResource;
use Exception;
use Illuminate\Http\JsonResponse;

class BackofficeDashboardController extends Controller
{
    /**
     *
     * @param BackofficeDashboardServiceInterface $dashboardService
     */
    public function __construct(
        private readonly BackofficeDashboardServiceInterface $dashboardService,
    ) {}

    /**
     *
     * @param BackofficeDashboardRequest $request
     * @return JsonResponse
     */
    public function summary(BackofficeDashboardRequest $request): JsonResponse
    {
        try {
            $data = $this->dashboardService->summary();
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error.'], 500);
        }

        return (new BackofficeDashboardResource($data))
            ->response()
            ->setStatusCode(200);
    }
}
