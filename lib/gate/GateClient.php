<?php

namespace lib\gate;

use GateApi\Configuration;
use GateApi\Api\WalletApi;
use GateApi\Api\SpotApi;
use GateApi\ObjectSerializer;
use GuzzleHttp\Client;
use lib\config\Load;

class GateClient
{
    private $walletApi;
    private $spotApi;

    /**
     * 构造函数 - 初始化 API 客户端
     *
     * @param string $apiKey API KEY
     * @param string $apiSecret API 密钥
     */
    public function __construct(string $apiKey = '', string $apiSecret = '')
    {
        if ($apiKey == '' || $apiSecret == '') {
            $apiKey = Load::get('gate.api_key');
            $apiSecret = Load::get('gate.api_secret');
        }
        // 配置 API 认证信息
        $config = Configuration::getDefaultConfiguration()
            ->setKey($apiKey)
            ->setSecret($apiSecret);

        // 初始化 API 客户端
        $httpClient = new Client();
        $this->walletApi = new WalletApi($httpClient, $config);
        $this->spotApi = new SpotApi($httpClient, $config);
    }


    /**
     * 获取账户余额
     *
     * @param bool $details 是否返回详细的余额信息（默认为 false）
     * @return array|bool|float|int|object|string
     */
    public function getBalance(bool $details = false)
    {
        try {
            // 传递参数，`details` 控制是否返回详细信息
            $params = ['details' => $details];

            // 调用 API 获取余额
            $totalBalance = $this->walletApi->getTotalBalance($params);
            return ObjectSerializer::sanitizeForSerialization($totalBalance);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取所有现货交易对
     *
     * @return array 交易对列表
     */
    public function getSpotPairs(): array
    {
        try {
            $pairs = $this->spotApi->listCurrencyPairs();
            $pairsArray = array_map(function ($pair) {
                return ObjectSerializer::sanitizeForSerialization($pair);
            }, $pairs);
            return $pairsArray;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取单个现货交易对的详细信息
     *
     * @param string $currencyPair 交易对名称，如 BTC_USDT
     * @return array|bool|float|int|object|string
        */
    public function getSpotPairInfo(string $currencyPair): array
    {
        try {
            $pairInfo = $this->spotApi->getCurrencyPair($currencyPair);
            return ObjectSerializer::sanitizeForSerialization($pairInfo);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取单个现货交易对的配置信息
     *
     * @param string $currencyPair 交易对名称，如 BTC_USDT
     * @return array|bool|float|int|object|string
      */

    public function getSpotPairConfig(string $currencyPair): array
    {
        try {
            $pairConfig = $this->spotApi->getCurrencyPairConfig($currencyPair);
            return ObjectSerializer::sanitizeForSerialization($pairConfig);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取单个现货交易对的 K 线数据
     *
     * @param string $currencyPair 交易对名称，如 BTC_USDT
     * @param string $interval 时间间隔，如 1min, 5min, 15min, 30min, 1hour, 2hour, 4hour, 6hour, 12hour, 1day, 3day, 7day
     * @param int $limit 限制返回的数据条数，最大 1000
     * @param int $startTime 开始时间戳（秒）
     * @param int $endTime 结束时间戳（秒）
     * @return array|bool|float|int|object|string
     */
    public function getSpotKline(string $currencyPair, string $interval, int $limit, int $startTime, int $endTime): array
    {
        try {
            $kline = $this->spotApi->getCurrencyPairKline($currencyPair, $interval, $limit, $startTime, $endTime);
            return ObjectSerializer::sanitizeForSerialization($kline);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取单个现货交易对的最新成交数据
     *
     * @param string $currencyPair 交易对名称，如 BTC_USDT
     * @return array|bool|float|int|object|string
     */
    public function getSpotTicker(string $currencyPair): array
    {
        try {
            $ticker = $this->spotApi->getCurrencyPairTicker($currencyPair);
            return ObjectSerializer::sanitizeForSerialization($ticker);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取单个现货交易对的深度数据
     *
     * @param string $currencyPair 交易对名称，如 BTC_USDT
     * @param int $limit 限制返回的数据条数，最大 1000
     * @return array|bool|float|int|object|string
     */
    public function getSpotDepth(string $currencyPair, int $limit): array
    {
        try {
            $depth = $this->spotApi->getCurrencyPairDepth($currencyPair, $limit);
            return ObjectSerializer::sanitizeForSerialization($depth);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
