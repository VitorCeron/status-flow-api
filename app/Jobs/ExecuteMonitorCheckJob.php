<?php

namespace App\Jobs;

use App\Domains\MonitorExecution\Services\Interfaces\MonitorExecutionServiceInterface;
use App\Enums\QueueEnum;
use App\Models\Monitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExecuteMonitorCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public int $timeout = 60;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 1;

    /**
     * @param Monitor $monitor
     */
    public function __construct(
        private readonly Monitor $monitor,
    ) {
        $this->onQueue(QueueEnum::DEFAULT->getQueueName());
    }

    /**
     * Execute the job.
     *
     * @param MonitorExecutionServiceInterface $monitorExecutionService
     * @return void
     */
    public function handle(MonitorExecutionServiceInterface $monitorExecutionService): void
    {
        $checkResult = $monitorExecutionService->executeCheck($this->monitor);
        $monitorExecutionService->processResult($this->monitor, $checkResult);
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('ExecuteMonitorCheckJob failed', [
            'monitor_id' => $this->monitor->id,
            'monitor_url'=> $this->monitor->url,
            'error'      => $exception->getMessage(),
        ]);
    }
}
