<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PosinRepository;
use App\Repositories\ItemRepository;
use App\Services\PosinService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 註冊Repository
        $this->app->bind(PosinRepository::class, function ($app) {
            return new PosinRepository($app->make(\App\Models\Posin::class));
        });

        $this->app->bind(ItemRepository::class, function ($app) {
            return new ItemRepository($app->make(\App\Models\Item::class));
        });

        // 註冊Service
        $this->app->bind(PosinService::class, function ($app) {
            return new PosinService(
                $app->make(PosinRepository::class),
                $app->make(ItemRepository::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}