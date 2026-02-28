<?php

namespace App\Domains\MonitorExecution\Services;

use App\Domains\MonitorExecution\Services\Interfaces\MonitorExecutionServiceInterface;
use App\Domains\MonitorLog\Services\Interfaces\MonitorLogServiceInterface;
use App\Domains\Monitor\Repositories\Interfaces\MonitorRepositoryInterface;
use App\Enums\MonitorStatusEnum;
use App\Mail\MonitorDownMail;
use App\Models\Monitor;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class MonitorExecutionService implements MonitorExecutionServiceInterface
{
    /**
     * @param MonitorRepositoryInterface $monitorRepository
     * @param MonitorLogServiceInterface $monitorLogService
     * @param Client $httpClient
     */
    public function __construct(
        private readonly MonitorRepositoryInterface $monitorRepository,
        private readonly MonitorLogServiceInterface $monitorLogService,
        private readonly Client $httpClient,
    ) {}

    /**
     * @inheritDoc
     */
    public function getMonitorsDueToRun(): Collection
    {
        return $this->monitorRepository->findDueToRun();
    }

    /**
     * @inheritDoc
     */
    public function executeCheck(Monitor $monitor): array
    {
        $checkedAt = Carbon::now();
        $startTime = microtime(true);

        try {
            $response = $this->httpClient->request($monitor->method->value, $monitor->url, [
                'timeout'         => $monitor->timeout,
                'connect_timeout' => $monitor->timeout,
                'http_errors'     => false,
            ]);

            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);
            $responseCode   = $response->getStatusCode();
            $status         = $responseCode === 200
                ? MonitorStatusEnum::UP->value
                : MonitorStatusEnum::DOWN->value;

            return [
                'status'           => $status,
                'response_code'    => $responseCode,
                'response_time_ms' => $responseTimeMs,
                'checked_at'       => $checkedAt,
            ];
        } catch (GuzzleException) {
            $responseTimeMs = (int) round((microtime(true) - $startTime) * 1000);

            return [
                'status'           => MonitorStatusEnum::DOWN->value,
                'response_code'    => null,
                'response_time_ms' => $responseTimeMs,
                'checked_at'       => $checkedAt,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function processResult(Monitor $monitor, array $checkResult): void
    {
        $previousStatus = $monitor->status;

        $this->monitorLogService->saveLog($monitor, $checkResult);

        $this->monitorRepository->update($monitor, [
            'status'          => $checkResult['status'],
            'last_checked_at' => $checkResult['checked_at'],
        ]);

        if ($checkResult['status'] === MonitorStatusEnum::UP->value) {
            return;
        }

        $consecutiveFailures = $this->monitorLogService->countConsecutiveFailures($monitor);
        $thresholdReached    = $consecutiveFailures >= $monitor->fail_threshold;
        $wasNotAlreadyDown   = $previousStatus !== MonitorStatusEnum::DOWN;

        if ($thresholdReached && $wasNotAlreadyDown) {
            Mail::to($monitor->notify_email)->send(new MonitorDownMail($monitor));
        }
    }
}
