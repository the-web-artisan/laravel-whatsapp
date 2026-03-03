<?php

namespace MissaelAnda\Whatsapp;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WhatsappServiceProvider extends ServiceProvider
{
    /**
     * Get the base path of the package (cross-platform compatible).
     */
    protected function packageBasePath(): string
    {
        $basePath = dirname(__DIR__);
        $resolved = realpath($basePath);

        return $resolved !== false ? $resolved : $basePath;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->packageBasePath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'whatsapp.php', 'whatsapp');

        $this->app->singleton('whatsapp', fn () => new Whatsapp(
            Config::get('whatsapp.default_number_id'),
            Config::get('whatsapp.token')
        ));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
        $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        if (Config::get('whatsapp.webhook.enabled')) {
            Route::group([
                'prefix' => Config::get('whatsapp.webhook.path'),
                'as' => 'whatsapp.',
            ], function () {
                $this->loadRoutesFrom($this->packageBasePath() . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php');
            });
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->packageBasePath() . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'whatsapp.php' => $this->app->configPath('whatsapp.php'),
            ], 'config');
        }
    }
}
