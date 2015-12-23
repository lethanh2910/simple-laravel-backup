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
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\BackupDatabase::class,
        \App\Console\Commands\BackupTidy::class,
        \App\Console\Commands\BackupRestore::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('inspire')
                 //->hourly();

        $schedule->command('backup:database')
                 ->daily();

        $schedule->command('backup:tidy')
                 ->dailyAt('1:00');
    }
}
