<?php
namespace app\Console;

class Kernel {
    public function commands(): array {
        return [
            'TestJob' => \app\Console\Commands\TestJob::class,
        ];
    }

    public function schedule() {
        // 简单判断当前时间每分钟跑一次任务
        (new \app\Console\Commands\TestJob())->run();
    }
}
