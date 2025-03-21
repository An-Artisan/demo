<?php

namespace lib\gate;

use GateApi\Configuration;
use GateApi\Api\WalletApi;
use GateApi\Api\SpotApi;
use GateApi\Model\TotalBalance;
use GuzzleHttp\Client;

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
    public function __construct($apiKey, $apiSecret)
    {
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
     * @return TotalBalance|array 返回账户余额数组
     */
    public function getBalance(bool $details = false)
    {
        try {
            // 传递参数，`details` 控制是否返回详细信息
            $params = ['details' => $details];

            // 调用 API 获取余额
            return $this->walletApi->getTotalBalance($params);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    /**
     * 获取所有现货交易对
     *
     * @return array 交易对列表
     */
    public function getSpotPairs()
    {
        try {
            $pairs = $this->spotApi->listCurrencyPairs();
            return $pairs;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
