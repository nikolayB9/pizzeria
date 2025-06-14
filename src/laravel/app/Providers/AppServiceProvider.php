<?php

namespace App\Providers;

use App\Repositories\Admin\AdminOrderRepositoryInterface;
use App\Repositories\Admin\EloquentAdminOrderRepository;
use App\Repositories\Api\V1\Address\AddressRepositoryInterface;
use App\Repositories\Api\V1\Address\EloquentAddressRepository;
use App\Repositories\Api\V1\Cart\CartRepositoryInterface;
use App\Repositories\Api\V1\Cart\EloquentCartRepository;
use App\Repositories\Api\V1\Category\CategoryRepositoryInterface;
use App\Repositories\Api\V1\Category\EloquentCategoryRepository;
use App\Repositories\Api\V1\City\CityRepositoryInterface;
use App\Repositories\Api\V1\City\EloquentCityRepository;
use App\Repositories\Api\V1\Order\EloquentOrderRepository;
use App\Repositories\Api\V1\Order\OrderRepositoryInterface;
use App\Repositories\Api\V1\Product\EloquentProductRepository;
use App\Repositories\Api\V1\Product\ProductRepositoryInterface;
use App\Repositories\Api\V1\Profile\EloquentProfileRepository;
use App\Repositories\Api\V1\Profile\ProfileRepositoryInterface;
use App\Services\Api\V1\Payment\PaymentInterface;
use App\Services\Api\V1\Payment\YooKassaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, EloquentCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(AddressRepositoryInterface::class, EloquentAddressRepository::class);
        $this->app->bind(CityRepositoryInterface::class, EloquentCityRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);

        $this->app->bind(AdminOrderRepositoryInterface::class, EloquentAdminOrderRepository::class);

        $this->app->bind(PaymentInterface::class, YooKassaService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
