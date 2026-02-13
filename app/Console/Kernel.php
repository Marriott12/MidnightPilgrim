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
        // Daily thought generation (optional)
        // $schedule->command('pilgrim:generate-daily-thought')->daily();
        
        // DISCIPLINE CONTRACT ENFORCEMENT - Check deadlines hourly
        $schedule->call(function () {
            $service = app(\App\Services\DisciplineContractService::class);
            $contracts = \App\Models\DisciplineContract::where('status', 'active')->get();
            
            foreach ($contracts as $contract) {
                $service->checkDeadlines($contract);
            }
        })->hourly()->name('discipline:check-deadlines');

        // MONTHLY RELEASE ENFORCEMENT - Check daily at 18:30
        $schedule->call(function () {
            $service = app(\App\Services\DisciplineContractService::class);
            $service->checkMonthlyReleaseDeadlines();
        })->dailyAt('18:30')->name('discipline:check-monthly-releases');

        // CONTRACT FINALIZATION - Check daily at 00:30
        $schedule->call(function () {
            $service = app(\App\Services\DisciplineContractService::class);
            $service->finalizeCompletedContracts();
        })->dailyAt('00:30')->name('discipline:finalize-contracts');
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
