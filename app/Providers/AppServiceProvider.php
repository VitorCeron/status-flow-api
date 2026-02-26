<?php

namespace App\Providers;

use App\Domains\Auth\Repositories\AuthRepository;
use App\Domains\Auth\Repositories\Interfaces\AuthRepositoryInterface;
use App\Domains\Auth\Services\AuthService;
use App\Domains\Auth\Services\Interfaces\AuthServiceInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    public function boot(): void
    {
        //
    }
}
