<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Repositories\ClientRepositoryInterface;
use App\Infrastructure\Repositories\ClientRepository;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Repositories\ProductRepository;
use App\Domain\Repositories\LojaRepositoryInterface;
use App\Infrastructure\Repositories\LojaRepository;
use App\Domain\Repositories\SacolaRepositoryInterface;
use App\Infrastructure\Repositories\SacolaRepository;
use App\Domain\Repositories\PedidoRepositoryInterface;
use App\Infrastructure\Repositories\PedidoRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(LojaRepositoryInterface::class, LojaRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(SacolaRepositoryInterface::class, SacolaRepository::class);
        $this->app->bind(PedidoRepositoryInterface::class, PedidoRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
