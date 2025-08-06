<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\EloquentGroupRepository;
use App\Repositories\EloquentUserRepository;
use App\Services\UserService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind Group Repository Interface to its implementation
        $this->app->bind(
            GroupRepositoryInterface::class,
            EloquentGroupRepository::class
        );

        // Bind User Repository Interface to its implementation
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        // Register User Service
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepositoryInterface::class),
                $app->make(\App\Services\Auth\OtpService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}