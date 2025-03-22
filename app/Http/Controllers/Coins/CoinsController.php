<?php

namespace app\Http\Controllers\Coins;

use app\Http\Controllers\BaseController;
use lib\config\Load;
use lib\gate\GateClient;
use app\Services\TradingPairService;

class CoinsController extends BaseController
{
    // 获取所有交易对列表
    public function getCurrencyList($f3)
    {
        $TradingPairService = new TradingPairService();
        $data = $TradingPairService->findAllActive();

        if ($data === false) {
            $this->error(500, "Failed to fetch data");
            return;
        }
        $this->success($data);
    }

    //获取单个币种信息的接口
    public function getCurrencyInfo($f3)
    {
        $client = new GateClient();
        // 获取单个币种的深度数据
        $currencyPair = $f3->get('GET.currency_pair') ?? 'BTC_USDT';
        $data = $client->getSpotTickers(['currency_pair' => $currencyPair]);

        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        $this->success($data);
    }

    //获取单个币种的K线数据接口
    public function getCurrencyKline($f3)
    {
        try {
            $client = new GateClient();

            // 获取参数，带默认值
            $currencyPair = $f3->get('GET.currency_pair') ?? 'BTC_USDT';
            $interval = $f3->get('GET.interval') ?? '1h';
            $limit = (int)($f3->get('GET.limit') ?? 100);
            $from = (int)($f3->get('GET.from') ?? 0);
            $to = (int)($f3->get('GET.to') ?? 0);

            // 组装请求参数
            $params = [
                'currency_pair' => $currencyPair,
                'interval' => $interval,
            ];

            // 逻辑：优先使用 from/to，如果没传才 fallback 到 limit
            if ($from > 0 || $to > 0) {
                if ($from > 0) $params['from'] = $from;
                if ($to > 0) $params['to'] = $to;
            } else {
                $params['limit'] = $limit;
            }
            // 调用 API
            $data = $client->listCandlesticks($params);

            // 返回数据
            $this->success($data);
        } catch (\Exception $e) {
            $this->error(500, '获取 K线数据失败: ' . $e->getMessage());
        }
    }


    //获取单个币种的深度数据接口
    public function getCurrencyDepth($f3)
    {
        $client = new GateClient();
        // 获取单个币种的深度数据
        $currencyPair = $f3->get('GET.currency_pair') ?? 'BTC_USDT';
        $interval = $f3->get('GET.interval') ?? '0.00000001';
        $limit = $f3->get('GET.limit') ?? 100;
        $data = $client->listOrderBook($currencyPair, $interval, $limit);
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        $this->success($data);
    }

    //获取单个币种的成交数据接口
    public function getCurrencyTrade($f3)
    {
        $client = new GateClient();
        // 获取单个币种的成交数据
        $currencyPair = $f3->get('GET.currency_pair') ?? 'BTC_USDT';
        $limit = $f3->get('GET.limit') ?? 100;
        $data = $client->getSpotTrades($currencyPair, $limit);
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        $this->success($data);
    }

    //获取单个币种的指数数据接口
    public function getIndexData($f3)
    {
        $client = new GateClient();
        // 获取单个币种的指数数据
        $currencyPair = $f3->get('GET.currency_pair') ?? 'BTC_USDT';
        $data = $client->getSpotIndex($currencyPair);
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        $this->success($data);
    }

}
