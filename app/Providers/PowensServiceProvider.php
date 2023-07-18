<?php

namespace App\Providers;

use App\Interfaces\PowensRepositoryInterface;
use App\Repository\PowensRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class PowensServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        App::bind(PowensRepositoryInterface::class, PowensRepository::class);
    }
}
