<?php

namespace App\Providers;

use App\Domain\Profile\Repositories\ProfileRepositoryInterface;
use App\Infrastructure\Repositories\EloquentProfileRepository;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\EloquentUserRepository;
use App\Domain\Item\Repositories\ItemRepositoryInterface;
use App\Infrastructure\Repositories\EloquentItemRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ProfileRepositoryInterfaceの実装クラスを登録
        $this->app->bind(ProfileRepositoryInterface::class, EloquentProfileRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, EloquentItemRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 開発環境でのみSQLログを有効化
        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::info(
                    $query->sql,
                    [
                        'bindings' => $query->bindings,
                        'time' => $query->time
                    ]
                );
            });
        }
    }
}
