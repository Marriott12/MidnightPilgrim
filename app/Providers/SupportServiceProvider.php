<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class SupportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('files', function () {
            return new Filesystem();
        });
    }

    public function boot()
    {
        // no-op
    }
}
