<?php

namespace Osik\HubtelLaravelSms;

use Illuminate\Support\ServiceProvider;

class HubtelSmsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/hubtel-sms.php' => config_path('hubtel-sms.php'),
        ], 'config');
    }

    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../config/hubtel-sms.php', 'hubtel-sms');

        // Register the service
        $this->app->singleton('hubtel-sms', function ($app) {
            return new HubtelSms(
                config('hubtel-sms.client_id'),
                config('hubtel-sms.client_secret'),
                config('hubtel-sms.sender_id')
            );
        });
    }
}