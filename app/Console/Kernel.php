<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\GenerateDailyThought::class,
        \App\Console\Commands\ReflectNote::class,
            \App\Console\Commands\PilgrimListen::class,
            \App\Console\Commands\PilgrimReflect::class,
            \App\Console\Commands\PilgrimRemember::class,
            \App\Console\Commands\PilgrimMemoryAudit::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('pilgrim:generate-daily-thought')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        if (file_exists(base_path('routes/console.php'))) {
            require base_path('routes/console.php');
        }
    }
}
