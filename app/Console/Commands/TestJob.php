<?php
namespace app\Console\Commands;

use app\Services\UserService;

class TestJob {

    // php artisan TestJob run
    protected $f3;

    public function __construct($f3) {
        $this->f3 = $f3;
    }

    public function handle() {
        echo "这是默认的任务处理方法\n";
    }

    public function run() {
        logger()->write("hello job ", 'info');
        $user =  new UserService();
        $users = $user->getUsers();
        logger()->write($users, 'info');
        echo "运行 TestJob::run 成功  \n";
    }

}
