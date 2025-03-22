<?php
namespace app\Console\Commands;

use app\Services\MatchingEngineService;

class MatchingEngineJob {

    // php artisan MatchingEngineJob run
    protected $f3;

    public function __construct($f3) {
        $this->f3 = $f3;
    }

    public function handle() {
        echo "撮合订单任务\n";
    }

    public function run() {
        logger()->write("撮合订单任务开始执行 job ", 'info');
        $MatchingEngineService = new MatchingEngineService();
        //暂时只撮合btc_usdt，后续可优化此处，将交易对id作为参数传入，增加更多币种
        $pairs_id = 'BTC_USDT';
        $MatchingEngineService->matchOrders($pairs_id);
        echo "运行 MatchingEngineJob::run 成功  \n";
    }

}
