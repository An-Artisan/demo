<?php
namespace app\Console\Commands;

class TestJob {

    // php artisan.php TestJob run
    protected $f3;

    public function __construct($f3) {
        $this->f3 = $f3;
    }

    public function handle() {
        echo "这是默认的任务处理方法\n";
    }

    public function run() {
        logger()->write("hello job ", 'info');
        echo "运行 TestJob::run 成功  \n";
    }

}
