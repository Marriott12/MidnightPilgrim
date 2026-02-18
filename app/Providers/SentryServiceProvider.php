<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use Sentry\Laravel\ServiceProvider as SentryLaravelServiceProvider;

class SentryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Sentry integration placeholder. Ensure Sentry is installed and configured via composer and config/services.php.
        // If using Sentry, register the provider in config/app.php and install sentry/sentry-laravel via composer.
    }
}