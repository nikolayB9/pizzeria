<?php

namespace App\Providers;

use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Cart\EloquentCartRepository;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Category\EloquentCategoryRepository;
use App\Repositories\Product\EloquentProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\User\EloquentUserRepository;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
