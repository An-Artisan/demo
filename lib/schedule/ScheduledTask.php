<?php
namespace lib\schedule;

class ScheduledTask {
    // 要执行的命令类名，例如 TestJob
    protected $job;

    // 要执行的方法名，例如 run 或 handle
    protected $method;

    // 表示任务的时间表达式，例如 "* *"、"dailyAt:03:00"
    protected $expression;

    /**
     * 构造函数，设置任务的类名和方法名
     * @param string $job  任务类名（不带命名空间）
     * @param string $method 要执行的方法名，默认为 handle
     */
    public function __construct(string $job, string $method = 'handle') {
        $this->job = $job;
        $this->method = $method;
    }

    /**
     * 设置为每分钟执行
     * 表示任务表达式为 "* *"
     */
    public function everyMinute(): ScheduledTask
    {
        $this->expression = '* *';
        return $this;
    }

    /**
     * 设置每天指定时间执行（格式：HH:mm）
     * 例如 dailyAt('03:00') 表示每天凌晨 3 点执行一次
     */
    public function dailyAt($time = '00:00'): ScheduledTask
    {
        $this->expression = 'dailyAt:' . $time;
        return $this;
    }

    /**
     * 判断当前时间是否符合任务执行条件
     * 返回 true 表示应该立即执行任务
     */
    public function shouldRunNow(): bool {
        // 当前时间（格式：HH:mm）
        $now = date('H:i');

        // 如果是 everyMinute，则始终执行
        if ($this->expression === '* *') {
            return true;
        }

        // 如果是 dailyAt 类型任务
        if (strpos($this->expression, 'dailyAt:') === 0) {
            // 提取表达式中的目标时间
            $target = explode(':', substr($this->expression, 8));
            // 检查当前时间是否等于目标时间
            return $now === sprintf('%02d:%02d', $target[0], $target[1]);
        }

        return false;
    }

    /**
     * 实际执行任务：实例化命令类并调用指定方法
     */
    public function run() {
        // 拼接完整类名，例如 app\Console\Commands\TestJob
        $class = "app\\Console\\Commands\\{$this->job}";

        // 创建任务类实例，并传入 F3 实例
        $instance = new $class(\Base::instance());

        // 如果该方法存在，就执行
        if (method_exists($instance, $this->method)) {
            $instance->{$this->method}();
        }
    }
}
