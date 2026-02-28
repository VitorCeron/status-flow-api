<?php

namespace App\Http\Controllers\Monitor;

use App\Domains\Monitor\Services\Interfaces\MonitorServiceInterface;
use App\Domains\MonitorLog\Services\Interfaces\MonitorLogServiceInterface;
use App\Exceptions\Monitor\MonitorNotFoundException;
use App\Exceptions\Monitor\UnauthorizedMonitorAccessException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Monitor\MonitorStatsResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MonitorStatsController extends Controller
{
    /**
     * @param MonitorServiceInterface $monitorService
     * @param MonitorLogServiceInterface $monitorLogService
     */
    public function __construct(
        private readonly MonitorServiceInterface $monitorService,
        private readonly MonitorLogServiceInterface $monitorLogService,
    ) {}

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            $monitor = $this->monitorService->show($request->user(), $id);
        } catch (MonitorNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (UnauthorizedMonitorAccessException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        $stats = $this->monitorLogService->getStats($monitor);

        return (new MonitorStatsResource($stats))
            ->response()
            ->setStatusCode(200);
    }
}
