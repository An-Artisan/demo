<?php

namespace app\Services;

use app\Models\TradingPairModel;
class TradingPairService
{
    protected $TradingPairModel;

    public function __construct()
    {
        $this->TradingPairModel = new TradingPairModel();
    }

    public function findAllActive(): array
    {
        return $this->TradingPairModel->findAllActive();
    }

    public function findById($id): array
    {
        return $this->TradingPairModel->findById($id);
    }

    //交易对数据批量入库
    public function insertTradingPairs($tradingPairs)
    {
        // 入库前先检查是否存在
        $existedTradingPairs = $this->TradingPairModel->findAllActive();
        $existedTradingPairs = array_column($existedTradingPairs, 'pair_id');
        $tradingPairs = array_filter($tradingPairs, function ($tradingPair) use ($existedTradingPairs) {
            return!in_array($tradingPair['pair_id'], $existedTradingPairs);
        });
        // 批量入库
         $this->TradingPairModel->insertBatch($tradingPairs);
    }

    //更新或者新增交易对数据
    public function saveOrUpdate($tradingPair)
    {
         $this->TradingPairModel->saveOrUpdate($tradingPair);
    }
}
