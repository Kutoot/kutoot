<?php

namespace App\Providers;

use App\Contracts\SmsContract;
use App\Services\Sms\SmsManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsContract::class, function ($app) {
            return new SmsManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Model::unguard();
        Model::shouldBeStrict(! $this->app->isProduction());

        Gate::before(static function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}