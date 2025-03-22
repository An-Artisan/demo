<?php

namespace app\Console\Commands;

use app\Services\TradingPairService;
use lib\gate\GateClient;
use app\Services\UserService;

class CleanTradingPairJob
{

    // php artisan CleanTradingPairJob run
    protected $f3;

    public function __construct($f3)
    {
        $this->f3 = $f3;
    }

    public function handle()
    {
        echo "同步交易对数据\n";
    }

    public function run()
    {
        logger()->write(" 开始运行 CleanTradingPairJob::run ", 'info');
        $client = new GateClient();
        $pairs = $client->getSpotPairs();
        if ($pairs) {
            $TradingPairService = new TradingPairService();

            foreach ($pairs as $pair) {
                $TradingPairService->saveOrUpdate($pair);
            }
        }
        logger()->write($pairs, 'info');
        echo "运行 CleanTradingPairJob::run 成功  \n";
    }

}
