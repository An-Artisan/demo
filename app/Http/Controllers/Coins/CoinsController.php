<?php

namespace app\Http\Controllers\Coins;

use app\Http\Controllers\BaseController;
use lib\config\Load;
use lib\gate\GateClient;

class CoinsController extends BaseController
{
    // 获取所有交易对列表
    public function getCurrencyList($f3)
    {
        $client = new GateClient();
        // 获取交易对
        $data = $client->getSpotPairs();

        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        $this->success($data);
    }

    //获取单个币种信息的接口
    public function getCurrencyInfo($f3)
    {
        $currency = $f3->get('GET.currency') ?? 'BTC';
        $client       = new GateClient();
        // 获取单交易对信息
        $data = $client->getCurrency($currency);

        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        $this->success($data);
    }

    //获取单个币种的K线数据接口
    public function getCurrencyKline($f3)
    {
        $client = new GateClient();
        // 获取单个币种的 K线数据
        $currencyPair = $f3->get('GET.currency_pair') ?? 'BTC_USDT';
        $interval     = $f3->get('GET.interval') ?? '1m';
        $limit        = $f3->get('GET.limit') ?? 100;
        $startTime    = $f3->get('GET.start_time') ?? 0;
        $endTime      = $f3->get('GET.end_time') ?? 0;
        $data         = $client->listCandlesticks($currencyPair, $interval, $limit, $startTime, $endTime);
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        $this->success($data);
    }


    //获取单个币种的深度数据接口
    public function getCurrencyDepth($f3)
    {
        $client = new GateClient();
        // 获取单个币种的深度数据
        $currencyPair = $f3->get('GET.currency_pair') ?? 'BTC_USDT';
        $interval     = $f3->get('GET.interval') ?? '0.00000001';
        $limit        = $f3->get('GET.limit') ?? 100;
        $data         = $client->listOrderBook($currencyPair,$interval, $limit);
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
        $limit        = $f3->get('GET.limit') ?? 100;
        $data         = $client->getSpotTrades($currencyPair, $limit);
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
        $data         = $client->getSpotIndex($currencyPair);
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        $this->success($data);
    }

}
