<?php


namespace Rgergo67\LaravelMailman;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class MailmanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-mailman.php', 'laravel-mailman');

        $this->app->singleton(Mailman::class, function ($app) {
            $config = config('laravel-mailman');
            return new Mailman(
                new Client([
                    "base_uri" => "{$config['host']}:{$config['port']}/{$config['api']}/",
                    "auth" => [
                        $config['admin_user'],
                        $config['admin_pass']
                    ]
                ])
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['mailman'];
    }

    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-mailman.php' => config_path('laravel-mailman.php')
        ], 'laravel-mailman-config');
    }
}