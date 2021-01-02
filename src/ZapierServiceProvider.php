<?php

namespace MedSchoolCoach\LumenZapier;

use Illuminate\Support\ServiceProvider;
use MedSchoolCoach\HttpClient\Request;

/**
 * Class ZapierServiceProvider
 * @package MedSchoolCoach\LumenZapier
 */
class ZapierServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->configure('zapier');
        $this->mergeConfigFrom(realpath(__DIR__ . '/../config/zapier.php'), 'zapier');

        $this->app->singleton(ZapierHook::class, function ($app) {
            return new ZapierHook(
                config('zapier.zaps.url'),
                config('zapier.zaps.group-id'),
                config('zapier.zaps.hooks'),
                app(Request::class));
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
