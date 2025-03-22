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
    public function getBalance(bool $details = false): array
    {
        try {
            $params = ['details' => $details];

            // 获取余额对象
            $balanceObj = $this->walletApi->getTotalBalance($params);

            // 转为数组（第一层）
            $balanceArray = ObjectSerializer::sanitizeForSerialization($balanceObj);
            $balanceArray = get_object_vars($balanceArray);

            // 如果包含 details，递归转成数组
            if (isset($balanceArray['details'])) {
                foreach ($balanceArray['details'] as $key => $value) {
                    $balanceArray['details'][$key] = get_object_vars($value);
                }
            }

            return $balanceArray;
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
            if ($pairsArray) {
                foreach ($pairsArray as $key => $value) {
                    $pairsArray[$key] = get_object_vars($value);
                }
            }
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
            return get_object_vars(ObjectSerializer::sanitizeForSerialization($pairInfo));
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取单个现货交易对的配置信息
     *
     * @param string $currency 币种名称，如 BTC
     * @return array|bool|float|int|object|string
     */

    public function getCurrency(string $currency): array
    {
        try {
            $currencyInfo = $this->spotApi->getCurrency($currency);
            return get_object_vars(ObjectSerializer::sanitizeForSerialization($currencyInfo));
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * K线
     * @param array $params
     * @return array|bool|float|int|object|string|null
     * @author liuqiang
     * @email  liuqiang@smzdm.com
     * @since  2025年03月22日17:59
     */
    public function listCandlesticks(
        array $params
    )
    {
        try {
            // 调用实际 API（假设你有注入好的 $this->spotApi 实例）
            $response = $this->spotApi->listCandlesticks($params);

            return \GateApi\ObjectSerializer::sanitizeForSerialization($response);

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * 获取单个现货交易对的深度数据
     *
     * @param string $currencyPair 交易对名称，如 BTC_USDT
     * @param string $interval 时间间隔，如 1min, 5min, 15min, 30min, 1hour, 2hour, 4hour, 6hour, 12hour, 1day, 3day, 7day
     * @param int $limit 限制返回的数据条数，最大 1000
     * @return array|bool|float|int|object|string
     */
    public function listOrderBook(string $currencyPair, string $interval, int $limit): array
    {
        try {
            $params = [
                'currency_pair' => $currencyPair,
                'interval' => $interval,
                'limit' => $limit,
            ];
            $depth = $this->spotApi->listOrderBook($params);

            return get_object_vars(ObjectSerializer::sanitizeForSerialization($depth));
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * 获取单个现货交易对的最新成交数据
     *
     * @param array $queryParams 交易对名称，如 BTC_USDT
     * @return array|bool|float|int|object|string
     */
    public function getSpotTickers(array $queryParams = [])
    {
        try {
            // 发起 GET 请求到 Gate.io API /spot/tickers 接口
            $response = $this->spotApi->listTickers($queryParams);

            // 转为标准结构数组返回
            return ObjectSerializer::sanitizeForSerialization($response);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

}
