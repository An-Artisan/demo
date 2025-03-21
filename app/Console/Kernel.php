<?php
namespace app\Console;

use lib\schedule\ScheduledTask;

class Kernel {
    public function commands(): array {
        return [
            'TestJob' => \app\Console\Commands\TestJob::class,
        ];
    }

    public function schedule() {
//        $f3 = \Base::instance();
//        (new \app\Console\Commands\TestJob($f3))->run();
        $tasks = [
            (new ScheduledTask('TestJob', 'run'))->everyMinute(),
            (new ScheduledTask('TestJob', 'handle'))->dailyAt('03:00'),
        ];

        foreach ($tasks as $task) {
            if ($task->shouldRunNow()) {
                $task->run();
            }
        }
    }
}
