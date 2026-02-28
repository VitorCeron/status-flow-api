<?php

namespace App\Console\Commands;

use App\Domains\MonitorExecution\Services\Interfaces\MonitorExecutionServiceInterface;
use App\Jobs\ExecuteMonitorCheckJob;
use Illuminate\Console\Command;

class RunMonitorChecksCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitors:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch check jobs for all active monitors that are due to run';

    /**
     * Execute the console command.
     *
     * @param MonitorExecutionServiceInterface $monitorExecutionService
     * @return int
     */
    public function handle(MonitorExecutionServiceInterface $monitorExecutionService): int
    {
        $monitors = $monitorExecutionService->getMonitorsDueToRun();

        foreach ($monitors as $monitor) {
            ExecuteMonitorCheckJob::dispatch($monitor);
        }

        $this->info("Dispatched {$monitors->count()} monitor check job(s).");

        return Command::SUCCESS;
    }
}
