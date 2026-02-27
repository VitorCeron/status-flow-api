<?php

namespace App\Http\Controllers\Monitor;

use App\Domains\Monitor\Services\Interfaces\MonitorServiceInterface;
use App\Exceptions\Monitor\MonitorNotFoundException;
use App\Exceptions\Monitor\UnauthorizedMonitorAccessException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Monitor\CreateMonitorRequest;
use App\Http\Requests\Monitor\ListMonitorFilterRequest;
use App\Http\Requests\Monitor\UpdateMonitorRequest;
use App\Http\Resources\Monitor\MonitorResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MonitorController extends Controller
{
    /**
     * @param MonitorServiceInterface $monitorService
     */
    public function __construct(
        private readonly MonitorServiceInterface $monitorService,
    ) {}

    /**
     * @param ListMonitorFilterRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(ListMonitorFilterRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $perPage   = (int) ($validated['per_page'] ?? 15);
        $filters   = array_filter(
            array_intersect_key($validated, array_flip(['status', 'is_active', 'method'])),
            fn ($v) => $v !== null,
        );

        $monitors = $this->monitorService->index($request->user(), $perPage, $filters);

        return MonitorResource::collection($monitors);
    }

    /**
     * @param CreateMonitorRequest $request
     * @return JsonResponse
     */
    public function store(CreateMonitorRequest $request): JsonResponse
    {
        $monitor = $this->monitorService->create($request->user(), $request->validated());

        return (new MonitorResource($monitor))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $monitor = $this->monitorService->show($request->user(), $id);
        } catch (MonitorNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (UnauthorizedMonitorAccessException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return (new MonitorResource($monitor))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * @param UpdateMonitorRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateMonitorRequest $request, string $id): JsonResponse
    {
        try {
            $monitor = $this->monitorService->update($request->user(), $id, $request->validated());
        } catch (MonitorNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (UnauthorizedMonitorAccessException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return (new MonitorResource($monitor))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->monitorService->delete($request->user(), $id);
        } catch (MonitorNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        } catch (UnauthorizedMonitorAccessException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(null, 204);
    }
}
