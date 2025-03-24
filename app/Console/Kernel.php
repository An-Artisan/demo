<?php

namespace app\Console;

use app\Console\Commands\MatchingEngineJob;
use app\Console\Commands\SyncRemainingJob;
use app\Console\Commands\SyncTradingPairJob;
use app\Console\Commands\TestJob;
use lib\schedule\ScheduledTask;


//crontab -e 配置  /usr/local/opt/php@7.1/bin/php /Users/artisan/PHP/demo/artisan schedule:run
//  /usr/local/opt/php@7.1/bin/php /Users/artisan/PHP/demo/artisan 修改自己的php目录和项目目录

class Kernel
{
    public function commands(): array
    {
        return [
            'TestJob' => TestJob::class,
            'SyncTradingPairJob' => SyncTradingPairJob::class,
            'MatchingEngineJob' => MatchingEngineJob::class,
            'SyncRemainingJob' => SyncRemainingJob::class,
        ];
    }

    public function schedule()
    {
        $tasks = [
//            (new ScheduledTask('TestJob', 'run'))->everyMinute(),
//            (new ScheduledTask('TestJob', 'run'))->dailyAt('03:00'),
//            (new ScheduledTask('TestJob', 'handle'))->dailyAt('03:00'),
        ];

        foreach ($tasks as $task) {
            if ($task->shouldRunNow()) {
                $task->run();
            }
        }
    }
}
