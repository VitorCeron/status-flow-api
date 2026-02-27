<?php

namespace App\Providers;

use App\Domains\Auth\Repositories\AuthRepository;
use App\Domains\Auth\Repositories\Interfaces\AuthRepositoryInterface;
use App\Domains\Auth\Services\AuthService;
use App\Domains\Auth\Services\Interfaces\AuthServiceInterface;
use App\Domains\Dashboard\Repositories\DashboardRepository;
use App\Domains\Dashboard\Repositories\Interfaces\DashboardRepositoryInterface;
use App\Domains\Dashboard\Services\DashboardService;
use App\Domains\Dashboard\Services\Interfaces\DashboardServiceInterface;
use App\Domains\Monitor\Repositories\MonitorRepository;
use App\Domains\Monitor\Repositories\Interfaces\MonitorRepositoryInterface;
use App\Domains\Monitor\Services\MonitorService;
use App\Domains\Monitor\Services\Interfaces\MonitorServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
        $this->app->bind(MonitorRepositoryInterface::class, MonitorRepository::class);
        $this->app->bind(MonitorServiceInterface::class, MonitorService::class);
    }

    public function boot(): void
    {
        //
    }
}
