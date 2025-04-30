<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Interface\UserServiceInterface;
use App\Services\Implementation\UserService;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(UserServiceInterface::class, UserService::class);

    }

    public function boot(): void
    {
       
    }
}
