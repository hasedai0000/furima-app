<?php

namespace App\Providers;

use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
use App\Infrastructure\Repositories\EloquentProfileRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // ProfileRepositoryInterfaceの実装クラスを登録
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
