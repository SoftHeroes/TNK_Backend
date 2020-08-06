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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('timer:createGames')
            ->dailyAt('02:00'); //timezone is asia/shanghai so it will 1pm in laos
        $schedule->command('socket:roadmap --loop=1 --limit=300')->everyMinute();
        $schedule->command('socket:roadmap --loop=5 --limit=300')->everyFiveMinutes();

        $schedule->command('crawler:stockBackup --loop=1')->dailyAt('01:00');
        $schedule->command('crawler:stockBackup --loop=5')->dailyAt('01:00');

        // Sending e-mail to developer when game crashed during calculating the game result.
        $schedule->command('crawler:gameCrashMailer')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
