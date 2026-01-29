<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        // no-op
    }

    public function boot()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }
}
