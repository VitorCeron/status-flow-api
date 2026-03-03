<?php

namespace App\Providers;

use App\Domains\Auth\Repositories\AuthRepository;
use App\Domains\Auth\Repositories\Interfaces\AuthRepositoryInterface;
use App\Domains\Auth\Services\AuthService;
use App\Domains\Auth\Services\Interfaces\AuthServiceInterface;
use App\Domains\Backoffice\Dashboard\Repositories\BackofficeDashboardRepository;
use App\Domains\Backoffice\Dashboard\Repositories\Interfaces\BackofficeDashboardRepositoryInterface;
use App\Domains\Backoffice\Dashboard\Services\BackofficeDashboardService;
use App\Domains\Backoffice\Dashboard\Services\Interfaces\BackofficeDashboardServiceInterface;
use App\Domains\Dashboard\Repositories\DashboardRepository;
use App\Domains\Dashboard\Repositories\Interfaces\DashboardRepositoryInterface;
use App\Domains\Dashboard\Services\DashboardService;
use App\Domains\Dashboard\Services\Interfaces\DashboardServiceInterface;
use App\Domains\Monitor\Repositories\MonitorRepository;
use App\Domains\Monitor\Repositories\Interfaces\MonitorRepositoryInterface;
use App\Domains\Monitor\Services\MonitorService;
use App\Domains\Monitor\Services\Interfaces\MonitorServiceInterface;
use App\Domains\MonitorExecution\Services\Interfaces\MonitorExecutionServiceInterface;
use App\Domains\MonitorExecution\Services\MonitorExecutionService;
use App\Domains\MonitorLog\Repositories\Interfaces\MonitorLogRepositoryInterface;
use App\Domains\MonitorLog\Repositories\MonitorLogRepository;
use App\Domains\MonitorLog\Services\Interfaces\MonitorLogServiceInterface;
use App\Domains\MonitorLog\Services\MonitorLogService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(BackofficeDashboardRepositoryInterface::class, BackofficeDashboardRepository::class);
        $this->app->bind(BackofficeDashboardServiceInterface::class, BackofficeDashboardService::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
        $this->app->bind(MonitorRepositoryInterface::class, MonitorRepository::class);
        $this->app->bind(MonitorServiceInterface::class, MonitorService::class);
        $this->app->bind(MonitorLogRepositoryInterface::class, MonitorLogRepository::class);
        $this->app->bind(MonitorLogServiceInterface::class, MonitorLogService::class);
        $this->app->bind(MonitorExecutionServiceInterface::class, MonitorExecutionService::class);
        $this->app->singleton(Client::class, fn () => new Client());
    }

    public function boot(): void
    {
        //
    }
}
