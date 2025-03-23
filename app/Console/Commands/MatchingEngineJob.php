<?php

namespace app\Console\Commands;

use app\Services\MatchingEngineService;

class MatchingEngineJob
{

    // php artisan MatchingEngineJob run
    protected $f3;

    public function __construct($f3)
    {
        $this->f3 = $f3;
    }

    public function handle()
    {
        echo "撮合订单任务\n";
    }

    public function run()
    {
        logger()->write("撮合订单任务开始执行 job", 'info');
        logger()->write("开始批量撮合交易对", 'info');

        $matchingEngineService = new MatchingEngineService();

        // 死循环，持续执行撮合逻辑
        while (true) {
            try {
                // 调用撮合方法
                $matchingEngineService->matchAllPairs();
            } catch (\Exception $e) {
                logger()->write("撮合发生异常：" . $e->getMessage(), 'error');
                // 视业务需求决定是否 break 或继续循环
                break;
            }

            // 可以根据业务需求调整休眠时长
            // 避免 CPU 空转占用过高
            sleep(1);
        }
    }

}
