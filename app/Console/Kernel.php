<?php

namespace App\Console;

use App\Jobs\CollectStreamStatsJob;
use App\Jobs\UpdateStreamStatusJob;
use App\Repositories\EventSession\EventSessionRepository;
use App\Repositories\EventSessionVisit\EventSessionVisitRepository;
use App\Repositories\NimbleStat\NimbleStatRepository;
use App\Repositories\Stream\StreamRepository;
use App\Repositories\StreamStat\StreamStatRepository;
use App\Repositories\User\UserRepository;
use App\Services\Fare\FareService;
use App\Services\WebSocket\WebSocketService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        logger()->info('Started every minutes jobs');

        $schedule->job(new UpdateStreamStatusJob(
            app()->make(StreamRepository::class),
            app()->make(EventSessionRepository::class),
            app()->make(WebSocketService::class),
            ))->everyMinute();
            
        $schedule->job(new CollectStreamStatsJob(
            app()->make(StreamStatRepository::class),
            app()->make(StreamRepository::class),
            app()->make(NimbleStatRepository::class),
            app()->make(WebSocketService::class),
            app()->make(EventSessionRepository::class),
            app()->make(UserRepository::class),
            app()->make(FareService::class),
            app()->make(EventSessionVisitRepository::class),
        ))->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
