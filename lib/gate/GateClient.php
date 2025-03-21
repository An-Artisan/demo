<?php

namespace lib\gate;

use GateApi\Configuration;
use GateApi\Api\WalletApi;
use GateApi\Api\SpotApi;
use GateApi\Model\TotalBalance;
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
     * @param string $apiKey API 密钥
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
             return  ObjectSerializer::sanitizeForSerialization($totalBalance);
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

    // 替换原来的全局函数 gateApiObjectToArray，封装为类内私有方法
    private function toArray($data) {
        if (is_array($data)) {
            return array_map([$this, 'toArray'], $data);
        }

        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return $this->toArray($data->toArray());
            }

            return $this->toArray(get_object_vars($data));
        }

        return $data;
    }
}





