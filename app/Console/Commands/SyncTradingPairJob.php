<?php

namespace app\Console\Commands;

use app\Services\TradingPairService;
use lib\gate\GateClient;

class SyncTradingPairJob
{
    // demo余额
//   {"spot":[{"locked":"0.00000000","currency":"BTC","available":"1000.00000000","update_id":123},{"locked":"0.00000000","currency":"ETH","available":"1000.00000000","update_id":124},{"locked":"0.00000000","currency":"USDT","available":"1000.00000000","update_id":125}],"total":{"amount":"82003000.00","borrowed":"0","currency":"USDT","unrealised_pnl":"0"},"details":{"spot":{"amount":"82001000.00","currency":"USDT"},"finance":{"amount":"1000.00","currency":"USDT"},"futures":{"amount":"1000.00","currency":"USDT"}}}
    // php artisan SyncTradingPairJob run
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
        logger()->write(" 开始运行 SyncTradingPairJob::run ", 'info');
        $client = new GateClient();
        $pairs = $client->getSpotPairs();
        if ($pairs) {
            $TradingPairService = new TradingPairService();

            foreach ($pairs as $pair) {
                $TradingPairService->saveOrUpdate($pair);
                logger()->write($pair['id'], '当前币种同步成功');
            }
        }
        logger()->write('数量' . count($pairs), '所有币种同步成功');
        echo "运行 SyncTradingPairJob::run 成功  \n";
    }

}
