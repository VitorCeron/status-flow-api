<?php

namespace App\Http\Controllers\Dashboard;

use App\Domains\Dashboard\Services\Interfaces\DashboardServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardRequest;
use App\Http\Resources\Dashboard\DashboardResource;
use Exception;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardServiceInterface $dashboardService,
    ) {}

    public function summary(DashboardRequest $request): JsonResponse
    {
        try {
            $data = $this->dashboardService->summary($request->user());
        } catch (Exception $e) {
            return response()->json(['message' => 'Internal server error.'], 500);
        }

        return (new DashboardResource($data))
            ->response()
            ->setStatusCode(200);
    }
}
