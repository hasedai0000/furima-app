<?php

namespace App\Providers;

use App\Domain\Profile\Services\UpdateProfileInformationService;
use App\Domain\User\Services\CreateUserService;
use App\Domain\User\Services\ResetUserPasswordService;
use App\Domain\User\Services\UpdateUserPasswordService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateUserService::class);

        Fortify::updateUserProfileInformationUsing(UpdateProfileInformationService::class);

        Fortify::updateUserPasswordsUsing(UpdateUserPasswordService::class);

        Fortify::resetUserPasswordsUsing(ResetUserPasswordService::class);

        // 新規登録後のリダイレクト先を設定
        Fortify::redirects('register', '/email/verify');

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
