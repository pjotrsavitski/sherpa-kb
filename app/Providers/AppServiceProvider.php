<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LanguageService;
use LanguageSeeder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LanguageService::class, function() {
            return new LanguageService;
        });
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
