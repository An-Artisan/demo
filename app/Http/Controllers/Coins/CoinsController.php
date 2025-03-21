<?php

namespace app\Http\Controllers\Coins;

use app\Http\Controllers\BaseController;
use lib\config\Load;
use lib\gate\GateClient;

class CoinsController extends BaseController
{
    // 获取所有法定货币列表
    public function getCurrencyList($f3)
    {
        //TODO 此处填充gateio请求的数据代码
        //  获取币种列表的 API 地址 $url = "https://api.gateio.ws/api/v4/spot/currencies";
        // 检查数据是否获取成功

        // 调用余额。
        $apiKey = Load::get('gate.api_key');
        $apiSecret = Load::get('gate.api_secret');
        $client = new GateClient($apiKey, $apiSecret);

        // ✅ 获取账户余额（不包含详细信息）
        $balance = $client->getBalance();
        dd($balance);

        $data = [];
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        // 返回 JSON 格式的币种列表
        header('Content-Type: application/json');
        $this->success($data);
    }

    //获取单个币种信息的接口
    public function getCurrencyInfo($f3)
    {
        //TODO 此处填充gateio请求的数据代码
        //  获取单个币种信息的 API 地址 $url = "https://api.gateio.ws/api/v4/spot/currency_pairs/BTC_USDT";
        // 检查数据是否获取成功
        $data = [];
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        // 返回 JSON 格式的单个币种信息
        header('Content-Type: application/json');
        $this->success($data);
    }

    //获取币种配置信息的接口
    public function getCurrencyConfig($f3)
    {
        //TODO 此处填充gateio请求的数据代码
        //  获取币种配置信息的 API 地址 $url = "https://api.gateio.ws/api/v4/spot/currency_pairs/BTC_USDT/config";
        // 检查数据是否获取成功
        $data = [];
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        // 返回 JSON 格式的币种配置信息
        header('Content-Type: application/json');
        $this->success($data);
    }

    //获取单个币种的K线数据接口
    public function getKlineData($f3)
    {
        //TODO 此处填充gateio请求的数据代码
        //  获取单个币种的K线数据 API 地址 $url = "https://api.gateio.ws/api/v4/spot/klines?currency_pair=BTC_USDT&interval=1d&limit=100";
        // 检查数据是否获取成功
        $data = [];
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        // 返回 JSON 格式的单个币种的K线数据
        header('Content-Type: application/json');
        $this->success($data);
    }

    //获取单个币种的深度数据接口
    public function getDepthData($f3)
    {
        //TODO 此处填充gateio请求的数据代码
        //  获取单个币种的深度数据 API 地址 $url = "https://api.gateio.ws/api/v4/spot/order_book?currency_pair=BTC_USDT";
        // 检查数据是否获取成功
        $data = [];
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        // 返回 JSON 格式的单个币种的深度数据
        header('Content-Type: application/json');
        $this->success($data);
    }

    //获取单个币种的成交数据接口
    public function getTradeData($f3)
    {
        //TODO 此处填充gateio请求的数据代码
        //  获取单个币种的成交数据 API 地址 $url = "https://api.gateio.ws/api/v4/spot/trades?currency_pair=BTC_USDT&limit=100";
        // 检查数据是否获取成功
        $data = [];
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        // 返回 JSON 格式的单个币种的成交数据
        header('Content-Type: application/json');
        $this->success($data);
    }

    //获取单个币种的指数数据接口
    public function getIndexData($f3)
    {
        //TODO 此处填充gateio请求的数据代码
        //  获取单个币种的指数数据 API 地址 $url = "https://api.gateio.ws/api/v4/spot/indexes?currency_pair=BTC_USDT";
        // 检查数据是否获取成功
        $data = [];
        if ($data === false) {
            $this->error(500, "Failed to fetch data from Gate.io API");
            return;
        }

        // 返回 JSON 格式的单个币种的指数数据
        header('Content-Type: application/json');
        $this->success($data);
    }

}
