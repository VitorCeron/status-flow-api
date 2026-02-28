<?php

namespace App\Console\Commands;

use App\Domains\MonitorLog\Services\Interfaces\MonitorLogServiceInterface;
use Illuminate\Console\Command;

class PruneMonitorLogsCommand extends Command
{
    /**
     * The number of days to retain monitor logs.
     */
    private const RETENTION_DAYS = 90;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitors:prune-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete monitor logs older than ' . self::RETENTION_DAYS . ' days';

    /**
     * Execute the console command.
     *
     * @param MonitorLogServiceInterface $monitorLogService
     * @return int
     */
    public function handle(MonitorLogServiceInterface $monitorLogService): int
    {
        $deleted = $monitorLogService->deleteOlderThan(self::RETENTION_DAYS);

        $this->info("Pruned {$deleted} monitor log(s) older than " . self::RETENTION_DAYS . " days.");

        return Command::SUCCESS;
    }
}
